#------------------------------------------------------------
#        Script MySQL.
#------------------------------------------------------------


#------------------------------------------------------------
# Table: quizz
#------------------------------------------------------------

CREATE TABLE quizz(
        id           int (11) Auto_increment  NOT NULL ,
        nom          Varchar (255) ,
        tokenWeb     Varchar (500) ,
        PRIMARY KEY (id ) ,
        UNIQUE (tokenWeb )
)ENGINE=InnoDB;


#------------------------------------------------------------
# Table: joueur
#------------------------------------------------------------

CREATE TABLE joueur(
        id         int (11) Auto_increment  NOT NULL ,
        pseudo     Varchar (255) ,
        PRIMARY KEY (id )
)ENGINE=InnoDB;


#------------------------------------------------------------
# Table: question
#------------------------------------------------------------

CREATE TABLE question(
        id          int (11) Auto_increment  NOT NULL ,
        enonce     Varchar (255) ,
        coefficient Int ,
        id_quizz    Int NOT NULL ,
        PRIMARY KEY (id ,id_quizz )
)ENGINE=InnoDB;


#------------------------------------------------------------
# Table: reponse
#------------------------------------------------------------

CREATE TABLE reponse(
        id          int (11) Auto_increment  NOT NULL ,
        nom         Varchar (255) ,
        estSolution Bool ,
        id_question Int NOT NULL ,
        id_quizz    Int NOT NULL ,
        PRIMARY KEY (id ,id_question ,id_quizz )
)ENGINE=InnoDB;


#------------------------------------------------------------
# Table: scores
#------------------------------------------------------------

CREATE TABLE scores(
        score     Int ,
        dateHeure Datetime ,
        typeJeu   Int ,
        id_joueur Int NOT NULL ,
        id_quizz  Int NOT NULL ,
        PRIMARY KEY (id_joueur ,id_quizz )
)ENGINE=InnoDB;

ALTER TABLE question ADD CONSTRAINT FK_question_id_quizz FOREIGN KEY (id_quizz) REFERENCES quizz(id);
ALTER TABLE reponse ADD CONSTRAINT FK_reponse_id_question FOREIGN KEY (id_question) REFERENCES question(id);
ALTER TABLE reponse ADD CONSTRAINT FK_reponse_id_quizz FOREIGN KEY (id_quizz) REFERENCES quizz(id);
ALTER TABLE scores ADD CONSTRAINT FK_scores_id_joueur FOREIGN KEY (id_joueur) REFERENCES joueur(id);
ALTER TABLE scores ADD CONSTRAINT FK_scores_id_quizz FOREIGN KEY (id_quizz) REFERENCES quizz(id);
