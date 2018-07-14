//Called only when a post is updated or created
function update_API( $post_id, $post, $update ) {
    
    //Act only if the post is a public event
    if ($post->post_status == "publish" && $post->post_type == "tribe_events") {
    
        //Find the featured image
        $image = split('"', get_the_post_thumbnail($post_id,"full",""))[5];
    
        //It was never inserted in the API --> POST
        if (get_post_meta($post_id, 'event_id', true) == "") {
           
            
            //Add the event
            $req = Requests::post(
                "{event_endpoint_url}", 
                [
                    'Authorization' => "{auth}",
                    'Content-Type' => "application/json"
                ],
                '{ 	"title": "' . $post->post_title . '"}', []
            );
            
            $response = json_decode($req->body);
            
            //Add the content
            $req = Requests::post(
                "{content_endpoint_url}", 
                [
                    'Authorization' => "{auth}",
                    'Content-Type' => "application/json"
                ],
                '{ 	"event_id": ' . $response->id . ', "title": "' . $post->post_title . '",	"text":"' . str_replace("\r", "", str_replace("\n", "<br>", str_replace('"', '\\"', $post->post_content))) . '"}', []
            );
            
            $responsecont = json_decode($req->body);
            
            //Add the date
            $req = Requests::post(
                "{content_endpoint_url}", 
                [
                    'Authorization' => "{auth}",
                    'Content-Type' => "application/json"
                ],
                '{ 	"event_id": ' . $response->id . ',	"start_date": "' . get_post_meta($post_id, '_EventStartDate', true) . '",	"start_hour": "' . get_post_meta($post_id, '_EventStartDate', true) . '", "end_date": "' . get_post_meta($post_id, '_EventEndDate', true) . '",  "end_hour": "' . get_post_meta($post_id, '_EventEndDate', true) . '"}', []
            );
            
            $responsedate = json_decode($req->body);
            
            //Add the image
            $req = Requests::post(
                "{content_endpoint_url}", 
                [
                    'Authorization' => "{auth}",
                    'Content-Type' => "application/json"
                ],
                '{"event_id": ' . $response->id . ', "path":"' . $image . '"}', []
            );
            
            $responseimage = json_decode($req->body);
            
            //Save the IDs
            
            add_post_meta($post_id, "event_id", $response->id, true);
            
            add_post_meta($post_id, "content_id", $responsecont->id, true);
            
            add_post_meta($post_id, "date_id", $responsedate->id, true);
            
            add_post_meta($post_id, "image_id", $responseimage->id, true);
            
        
        	
        }
        //It has already been inserted --> PUT
        else {
            
            //Find the IDs
            
            $event_id = get_post_meta($post_id, "event_id", true);
            
            $content_id = get_post_meta($post_id, "content_id", true);
            
            $date_id = get_post_meta($post_id, "date_id", true);
            
            $image_id = get_post_meta($post_id, "image_id", true);
            
            //Update the event
            $req = Requests::put(
                "{content_endpoint_url}", 
                [
                    'Authorization' => "{auth}",
                    'Content-Type' => "application/json"
                ],
                '{ "id":' . $event_id . ',	"title": "' . $post->post_title . '"}', []
            );
            
            
            //Update the content
            $req = Requests::put(
                "{content_endpoint_url}", 
                [
                    'Authorization' => "{auth}",
                    'Content-Type' => "application/json"
                ],
                '{ "id":' . $content_id . ',	"event_id": ' . $event_id . ',	"title": "' . $post->post_title . '",	"text":"' . $post->post_content . '"}', []
            );
            
            
            //Update the dates
            $req = Requests::put(
                "{content_endpoint_url}", 
                [
                    'Authorization' => "{auth}",
                    'Content-Type' => "application/json"
                ],
                '{ "id":' . $date_id . ',	"event_id": ' . $event_id . ',	"start_date": "' . get_post_meta($post_id, '_EventStartDate', true) . '",	"start_hour": "' . get_post_meta($post_id, '_EventStartDate', true) . '", "end_date": "' . get_post_meta($post_id, '_EventEndDate', true) . '",  "end_hour": "' . get_post_meta($post_id, '_EventEndDate', true) . '"}', []
            );
            
            //Update the image
            $req = Requests::put(
                "{content_endpoint_url}", 
                [
                    'Authorization' => "{auth}",
                    'Content-Type' => "application/json"
                ],
                '{ "id":' . $image_id . ',	"event_id": ' . $event_id . ',	"path":"' . $image . '"}', []
            );
            
            
            
        }
    }
    
}
add_action( 'wp_insert_post', 'update_API', 10, 3 );

function req_DELETE($event_id, $content_id, $date_id, $image_id) {
    
    if ($event_id != "") {
        $data_string = '{ "id": ' . $event_id . ' }';
        
        $url = '{content_endpoint_url}';
    
		//Here it used curl because the normal requests didn't work with the requested format
	
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: {auth}')
        );
        $result = curl_exec($ch);
        curl_close($ch);
    }
    
    if ($content_id != "") {
        $data_string = '{ "id": ' . $content_id . ' }';
        
        $url = '{content_endpoint_url}';
    
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: {auth}')
        );
        $result = curl_exec($ch);
        curl_close($ch);
    }
    
    if ($date_id != "") {
        $data_string = '{ "id": ' . $date_id . ' }';
        
        $url = '{content_endpoint_url}';
    
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: {auth}')
        );
        $result = curl_exec($ch);
        curl_close($ch);
    }
    
    if ($image_id != "") {
        $data_string = '{ "id": ' . $image_id . ' }';
        
        $url = '{content_endpoint_url}';
    
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: {auth}')
        );
        $result = curl_exec($ch);
        curl_close($ch);
    }
}


//Called when a post is deleted
function delete_API($post_id) {
    
            //Find the IDs
            
            $event_id = get_post_meta($post_id, "event_id", true);
            
            $content_id = get_post_meta($post_id, "content_id", true);
            
            $date_id = get_post_meta($post_id, "date_id", true);
            
            $image_id = get_post_meta($post_id, "image_id", true);
            
            //Delete what is found
            req_DELETE($event_id, $content_id, $date_id, $image_id);
    
}
add_action( 'before_delete_post', 'delete_API');