<?php

$message_tpl = functions::mdl_load("templates-e/message.tpl");

if (isset($_POST["submit"])) {

    $returnMessage = null;


    $user = new user();
    $user->setId($authData["id"]);

    // Email Confirmation
    if (isset($_POST["email"]) && !empty($_POST["email"])) {
        if ($_POST["email"] == $_POST["checkemail"]) {
            $user->setEmail($_POST["email"]);
            $email_success = true;
        }else {
            $returnMessage .= str_replace(
                "{c2r-lg-message}",
                $mdl_lang["email"]["check_failure"],
                $message_tpl
            );
        }
    }else {
        $user->setEmail($authData["email"]);
        $email_success = true;
    }

    // Password Confirmation
    if(!empty($_POST["oldPassword"])) {
        if(user::getSecurePassword($_POST["oldPassword"]) == $authData["password"]) {
            if(!empty($_POST["newPassword"])) {
                if($_POST["newPassword"] == $_POST["checkPassword"]){
                    $user->setPassword($_POST["newPassword"]);
                    $pw_success = true;
                }else {
                    $returnMessage .= str_replace(
                    [
                        "{c2r-message-type}",
                        "{c2r-lg-message}",
                    ],
                    [
                        "danger",
                        $mdl_lang["password"]["check_pw_failure"],
                    ],
                        $message_tpl
                    );
                }
            }else {
                $returnMessage = str_replace(
                [
                    "{c2r-message-type}",
                    "{c2r-lg-message}"
                ],
                [
                    "danger",
                    $mdl_lang["password"]["empty"]
                ],
                    $message_tpl
                );
            }
        }else {
            $returnMessage .= str_replace(
            [
                "{c2r-message-type}",
                "{c2r-lg-message}",
            ],
            [
                "danger",
                $mdl_lang["password"]["old_pw_failure"],
            ],
                $message_tpl
            );
        }
    }else {
        $user->setOldPassword($authData["password"]);
        $pw_success = true;
    }

    if(isset($pw_success) && isset($email_success)) {
        $user->setUsername($authData["username"]);
        $user->setRank($authData["rank"]);
        $user->setCode($authData["code"]);
        $user->setStatus($authData["status"]);
        $user->setUserKey();
        $user->setDate($authData["date"]);
        $user->setDateUpdate();

        if($user->update()) {
            $returnMessage = str_replace (
            [
                "{c2r-message-type}",
                "{c2r-lg-message}"
            ],
            [
                "success",
                $mdl_lang["account"]["success"]
            ],
            $message_tpl
        );

        $userData = $user->returnOneUser();
        $authData = $userData;

        $value = "{$authData["id"]}.{$userData["password"]}";

        setcookie (
            $cfg->system->cookie,
            $value,
            time() + (3600 * $cfg->system->cookie_time),
            (!empty($cfg->system->path_bo)) ? $cfg->system->path_bo : "/"
        );
    }else {
        $returnMessage = str_replace (
        [
            "{c2r-message-type}",
            "{c2r-lg-message}"
        ],
        [
            "danger",
            sprintf($mdl_lang["account"]["failure"], $cfg->email->support)
        ],
        $message_tpl
    );
    }
        //$user->insert()
    }

}


$mdl = str_replace(
    [
        "{c2r-return-message}",

        "{c2r-lg-username}",
        "{c2r-username}",
        "{c2r-lg-email}",
        "{c2r-email}",
        "{c2r-lg-rank}",
        "{c2r-rank}",
        "{c2r-lg-date}",
        "{c2r-date}",
        "{c2r-lg-password}",
        "{c2r-lg-email-change}",
        "{c2r-lg-save}",
        "{c2r-lg-cancel}",
        "{c2r-md5-email}"
    ],
    [
        (isset($returnMessage) && !empty($returnMessage)) ? $returnMessage : null,

        $mdl_lang["account"]["username"],
        $authData["username"],
        $mdl_lang["account"]["email"],
        $authData["email"],
        $mdl_lang["account"]["rank"],
        $authData["rank"],
        $mdl_lang["account"]["date"],
        $authData["date"],
        $mdl_lang["account"]["password"],
        $mdl_lang["account"]["email_change"],
        $lang["common"]["save"],
        $lang["common"]["cancel"],
        md5($authData["email"])
    ],
    functions::mdl_load("templates/home.tpl")
);

include "pages/module-core.php";
