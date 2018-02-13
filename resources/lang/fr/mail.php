<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Emails Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used by the global website.
    |
    */

    // mail_footer
    'mail_footer_content'           => ':domain, un site internet pour l\'emploi. Simple, rapide et efficace.',


    // ad_posted
    'ad_posted_title'               => 'Activez votre annonce ":title"',
    'ad_posted_content_1'           => 'Bonjour, <br><br>Merci de bien vouloir cliquer sur le bouton ci-dessous pour confirmer votre annonce. Le lien du bouton vous redirige sur la version internet classique de notre site en fonction de l\'appareil que vous utilisez:',
    'ad_posted_content_2'           => 'Activez votre annonce',
    'ad_posted_content_3'           => 'Dans certains cas, le lien du bouton est inactif. Veuillez alors copier le lien ci-dessous dans la barre d\'adresse de votre navigateur Internet:<br><a href=":activationLink">:activationLink</a><br><br>Dans les 24 heures suivant votre confirmation, votre annonce sera relue par notre équipe éditoriale pour validation et vous recevrez un email de validation une fois votre annonce en ligne.<br><br>Merci de votre confiance et à très bientôt sur notre site,<br><br>L\'équipe <a href=":countryDomain">:domain</a><br><a href=":domain">:domain</a><br><br><br>PS: Ceci est un email automatique, merci de ne pas y répondre.',


    // ad_deleted
    'ad_deleted_title'              => 'Votre annonce ":title" a bien été supprimée',
    'ad_deleted_content'            => 'Bonjour,<br><br>Votre annonce ":title" a bien été supprimée de <a href=":countryDomain">:domain</a> le :now.<br><br>Merci de votre confiance et à très bientôt sur notre site,<br><br>L\'équipe <a href=":countryDomain">:domain</a><br><a href=":domain">:domain</a><br><br><br>PS: Ceci est un email automatique, merci de ne pas y répondre.',


    // ad_employer_contacted
    'ad_employer_contacted_title'   => 'Votre annonce ":title" sur :app_name',
    'ad_employer_contacted_content' => '<strong>Coordonnées du contact :</strong><br>Nom : :name<br>Email : :email<br>Tel : :phone<br><br>Cet email vous a été envoyé au sujet de l\'annonce ":title" que vous avez déposée sur <a href=":countryDomain">:domain</a> : <a href=":urlAd">:urlAd</a><br><br>PS : la personne qui vous a contacté ne connaîtra pas votre email tant que vous ne lui aurez pas répondu.<br><br>Pensez à toujours vérifier les coordonnées de votre interlocuteur (nom, prénom, adresse, ...) afin de vous assurer d\'avoir un contact en cas de litige. D\'une manière générale, privilégiez la remise de l\'objet en mains propres.<br><br>Méfiez-vous des offres trop alléchantes ! Soyez vigilants avec les demandes provenant de l\'étranger quand vous ne disposez que d\'un contact par email. Le virement bancaire par Western Union ou Mandat Cash proposé risque bien d\'être factice.<br><br>Merci de votre confiance et à très bientôt sur notre site,<br><br>L\'équipe <a href=":countryDomain">:domain</a><br><a href=":domain">:domain</a><br><br><br>PS: Ceci est un email automatique, merci de ne pas y répondre.',


    // user_deleted
    'user_deleted_title'            => 'Votre compte a bien été supprimé',
    'user_deleted_content'          => 'Bonjour,<br><br>Votre compte a bien été supprimée de <a href=":countryDomain">:domain</a> le :now.<br><br>Merci de votre confiance et à très bientôt sur notre site,<br><br>L\'équipe <a href=":countryDomain">:domain</a><br><a href=":domain">:domain</a><br><br><br>PS: Ceci est un email automatique, merci de ne pas y répondre.',


    // user_registered
    'user_registered_title'         => 'Bienvenue sur :app_name !',
    'user_registered_content_1'     => 'Bienvenue sur :app_name :user_name !',
    'user_registered_content_2'     => 'Cliquez sur le bouton ci-dessous pour activer votre compte.',
    'user_registered_content_3'     => 'Activer mon compte',
    'user_registered_content_4'     => 'Dans certains cas, le lien du bouton est inactif. Veuillez alors copier le lien ci-dessous dans la barre d\'adresse de votre navigateur Internet:<br><a href=":activationLink">:activationLink</a><br><br><strong>Attention, l\'équipe de :app_name vous recommande de :</strong><br><br>1 - Toujours se méfier des annonceurs refusant de vous faire voir le bien mis en vente ou en location,<br>2 - Ne jamais envoyer d\'argent par Western Union ou autre mandat international.<br><br>Si vous avez un doute concernant le sérieux d\'un annonceur, contactez-nous immédiatement. Nous pourrons ainsi le neutraliser au plus vite et éviter qu\'une personne moins avisée n\'en devienne la victime.<br><br>Merci de votre confiance et à très bientôt sur notre site,<br><br>L\'équipe <a href=":countryDomain">:domain</a><br><a href=":domain">:domain</a><br><br><br>PS: Ceci est un email automatique, merci de ne pas y répondre.',


    // reset_password
    'reset_password_title'          => 'Réinitialisez votre mot de passe',
    'reset_password_content'        => 'Mot de passe oublié? Vous pouvez en obtenir un nouveau.',


    // contact_form
    'contact_form_title'            => 'Nouveau message de :app_name',
    'contact_form_content'          => ':app_name - Nouveau message',


    // ad_report_sent
    'ad_report_sent_title'          => 'Nouveau report d\'abus - :app_name/:country_code',
    'ad_report_sent_content'        => 'Nouveau report d\'abus - :app_name/:country_code',
    'Ad URL'                        => 'URL de l\'annonce',


    // ad archived
    'ad_archived_title'             => 'Your ad ":title" has been archived',
    'ad_archived_content'           => 'Hello,<br><br>Your ad ":title" has been archived from :domain at :now.<br><br>You can repost it by clicking here : :repostLink <br><br>If you do nothing your ad will be permanently deleted on :dateDel.<br><br>Thank you for your trust and see you soon,<br><br>The :domain Team<br>:domain<br><br><br>PS: This is an automated email, please don\'t reply.',


    // ad_will_be_deleted
    'ad_will_be_deleted_title'      => 'Your ad ":title" will be deleted in :days days',
    'ad_will_be_deleted_content'    => '',


    // ad_sent_by_email
    'ad_sent_by_email_title'        => 'Nouvelle Suggestion - :app_name/:country_code',
    'ad_sent_by_email_content'      => 'Un utilisateur vous a recommandé le lien d\'une offre d\'emploi avec l\'adresse email: :sender_email<br>Cliquez ci-dessous pour voir les détails de l\'offre d\'emploi.',
    'Job URL'                       => 'URL de l\'annonce',


    // ad_notification
    'ad_notification_title'         => 'Une offre vient d\'être posté,',
    'ad_notification_content'       => 'Bonjour Admin,<br><br>L\'utilisateur :advertiser_name vient de poster une nouvelle offre d\'emploi.<br>Titre de l\'annonce: :title<br>Publiée le: :now à :time<br><br>Meilleures salutations,<br><br>L\'équipe :domain',


    // user_notification
    'user_notification_title'       => 'Un nouvel utilisateur',
    'user_notification_content'     => 'Bonjour Admin,<br><br>:name vient de s\'inscrire.<br>Inscrit le: :now à :time<br>Email: <a href="mailto::email">:email</a><br><br>Meilleures salutations,<br><br>L\'équipe :domain',


    // payment_sent
    'payment_sent_title'            => 'Merci pour votre paiement !',
    'payment_sent_content'          => 'Bonjour,<br><br>Nous avons bien reçu votre paiement pour l\'annonce ":title".<br><h1>Merci !</h1><br>Meilleures salutations,<br><br>L\'équipe :domain',


    // payment_notification
    'payment_notification_title'    => 'Un paiement vient d\'être effectué',
    'payment_notification_content'  => 'Bonjour Admin,<br><br>L\'utilisateur :advertiser_name vient de payer un package pour son annonce ":title".<br><br><strong>Détails du Pack</strong><br>Nom: :name<br>Tarif: :price<br><br>Meilleures salutations,<br><br>L\'équipe :domain',


];
