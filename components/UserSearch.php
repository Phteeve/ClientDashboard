<?php

class UserSearch {
	public static function render($action) {
		echo('
            <form action="' . $action . '" method="POST">
                <label for="name">Client Name:</label>
                <br />
                <input type="search" name="name" />
                <br />
                <button type="submit">Search</button>
            </form>
    	');
	}
}