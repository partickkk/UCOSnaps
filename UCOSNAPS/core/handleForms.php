<?php  
require_once 'dbConfig.php';
require_once 'models.php';

if (isset($_POST['insertNewUserBtn'])) {
	$username = trim($_POST['username']);
	$first_name = trim($_POST['first_name']);
	$last_name = trim($_POST['last_name']);
	$password = trim($_POST['password']);
	$confirm_password = trim($_POST['confirm_password']);

	if (!empty($username) && !empty($first_name) && !empty($last_name) && !empty($password) && !empty($confirm_password)) {

		if ($password == $confirm_password) {

			$insertQuery = insertNewUser($pdo, $username, $first_name, $last_name, password_hash($password, PASSWORD_DEFAULT));
			$_SESSION['message'] = $insertQuery['message'];

			if ($insertQuery['status'] == '200') {
				$_SESSION['message'] = $insertQuery['message'];
				$_SESSION['status'] = $insertQuery['status'];
				header("Location: ../login.php");
			}

			else {
				$_SESSION['message'] = $insertQuery['message'];
				$_SESSION['status'] = $insertQuery['status'];
				header("Location: ../register.php");
			}

		}
		else {
			$_SESSION['message'] = "Please make sure both passwords are equal";
			$_SESSION['status'] = '400';
			header("Location: ../register.php");
		}

	}

	else {
		$_SESSION['message'] = "Please make sure there are no empty input fields";
		$_SESSION['status'] = '400';
		header("Location: ../register.php");
	}
}

if (isset($_POST['loginUserBtn'])) {
	$username = trim($_POST['username']);
	$password = trim($_POST['password']);

	if (!empty($username) && !empty($password)) {

		$loginQuery = checkIfUserExists($pdo, $username);
		$userIDFromDB = $loginQuery['userInfoArray']['user_id'];
		$usernameFromDB = $loginQuery['userInfoArray']['username'];
		$passwordFromDB = $loginQuery['userInfoArray']['password'];

		if (password_verify($password, $passwordFromDB)) {
			$_SESSION['user_id'] = $userIDFromDB;
			$_SESSION['username'] = $usernameFromDB;
			header("Location: ../index.php");
		}

		else {
			$_SESSION['message'] = "Username/password invalid";
			$_SESSION['status'] = "400";
			header("Location: ../login.php");
		}
	}

	else {
		$_SESSION['message'] = "Please make sure there are no empty input fields";
		$_SESSION['status'] = '400';
		header("Location: ../register.php");
	}

}

if (isset($_GET['logoutUserBtn'])) {
	unset($_SESSION['user_id']);
	unset($_SESSION['username']);
	header("Location: ../login.php");
}


if (isset($_POST['insertPhotoBtn'])) {
    // Get Description
    $description = trim($_POST['photoDescription']);

    // Check if the file upload is successful
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        // Get file name and extension
        $fileName = $_FILES['image']['name'];
        $tempFileName = $_FILES['image']['tmp_name'];
        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);

        // Generate a unique image name
        $uniqueID = sha1(md5(uniqid(rand(), true)));
        $imageName = $uniqueID . "." . $fileExtension;

        // Ensure the images directory exists
        $folder = "../images/";
        if (!is_dir($folder)) {
            mkdir($folder, 0777, true); // Create directory with full permissions
        }

        // If we're editing a photo
        $photo_id = isset($_POST['photo_id']) ? $_POST['photo_id'] : "";

        // Save image record to the database
        $saveImgToDb = insertPhoto($pdo, $imageName, $_SESSION['username'], $description, $photo_id);

        // Store the actual image file
        if ($saveImgToDb) {
            $filePath = $folder . $imageName;
            if (move_uploaded_file($tempFileName, $filePath)) {
                header("Location: ../index.php");
                exit();
            } else {
                $_SESSION['message'] = "Failed to save the file to the server.";
                $_SESSION['status'] = '500';
                header("Location: handleForms.php"); // Redirect back to the same page
                exit();
            }
        } else {
            $_SESSION['message'] = "Failed to save image information to the database.";
            $_SESSION['status'] = '500';
            header("Location: handleForms.php"); // Redirect back to the same page
            exit();
        }
    } else {
        $_SESSION['message'] = "File upload error: " . $_FILES['image']['error'];
        $_SESSION['status'] = '400';
        header("Location: handleForms.php"); // Redirect back to the same page
        exit();
    }
}


if (isset($_POST['deletePhotoBtn'])) {
	$photo_name = $_POST['photo_name'];
	$photo_id = $_POST['photo_id'];
	$deletePhoto = deletePhoto($pdo, $photo_id);

	if ($deletePhoto) {
		unlink("../images/".$photo_name);
		header("Location: ../index.php");
	}

}

if (isset($_POST['editAlbumBtn'])) {
    $album_id = trim($_POST['album_id']);
    $album_name = trim($_POST['album_name']);

    if (!empty($album_id) && !empty($album_name)) {
        // Update the album name in the database
        $updateAlbum = updateAlbumName($pdo, $album_id, $album_name);

        if ($updateAlbum) {
            $_SESSION['message'] = "Album name updated successfully.";
            $_SESSION['status'] = '200';
            header("Location: ../albums.php"); // Redirect to albums page
        } else {
            $_SESSION['message'] = "Failed to update album name.";
            $_SESSION['status'] = '500';
            header("Location: ../albums.php"); // Redirect to albums page
        }
    } else {
        $_SESSION['message'] = "Please provide all required fields.";
        $_SESSION['status'] = '400';
        header("Location: ../albums.php"); // Redirect to albums page
    }
}