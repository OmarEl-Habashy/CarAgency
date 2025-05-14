-- Create Schema (or Database)
CREATE SCHEMA IF NOT EXISTS ConPay;
USE
ConPay; -- Select the schema to use

-- Create User Table
CREATE TABLE Users
(
    UserID    INT AUTO_INCREMENT PRIMARY KEY,
    Username  VARCHAR(255) NOT NULL UNIQUE, -- Added UNIQUE constraint
    Email     VARCHAR(255) NOT NULL UNIQUE, -- Added UNIQUE constraint
    PassW     VARCHAR(255) NOT NULL,        -- Consider storing a hash, not plain text
    Bio       TEXT,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create Post Table
CREATE TABLE Posts
(
    PostID     INT AUTO_INCREMENT PRIMARY KEY,                       -- Changed from SERIAL
    UserID     INT NOT NULL,
    ContentURL VARCHAR(255),
    Caption    TEXT,
    CreatedAt  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (UserID) REFERENCES Users (UserID) ON DELETE CASCADE -- Added ON DELETE CASCADE
);

-- Create Comment Table
CREATE TABLE Comments
(
    CommentID INT AUTO_INCREMENT PRIMARY KEY,                         -- Changed from SERIAL
    PostID    INT  NOT NULL,
    UserID    INT  NOT NULL,
    Content   TEXT NOT NULL,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (PostID) REFERENCES Posts (PostID) ON DELETE CASCADE, -- Added ON DELETE CASCADE
    FOREIGN KEY (UserID) REFERENCES Users (UserID) ON DELETE CASCADE  -- Added ON DELETE CASCADE
);

-- Create Like Table
CREATE TABLE Likes
(
    LikeID    INT AUTO_INCREMENT PRIMARY KEY,                         -- Changed from SERIAL
    PostID    INT NOT NULL,
    UserID    INT NOT NULL,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (PostID) REFERENCES Posts (PostID) ON DELETE CASCADE, -- Added ON DELETE CASCADE
    FOREIGN KEY (UserID) REFERENCES Users (UserID) ON DELETE CASCADE, -- Added ON DELETE CASCADE
    UNIQUE KEY `unique_like` (`PostID`,`UserID`)                      -- Prevent duplicate likes
);

-- Create Follow Table
CREATE TABLE Follows
(
    FollowerID INT NOT NULL,
    FolloweeID INT NOT NULL,
    CreatedAt  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (FollowerID, FolloweeID),
    FOREIGN KEY (FollowerID) REFERENCES Users (UserID) ON DELETE CASCADE, -- Added ON DELETE CASCADE
    FOREIGN KEY (FolloweeID) REFERENCES Users (UserID) ON DELETE CASCADE  -- Added ON DELETE CASCADE
);

-- Insert Users
INSERT INTO Users (Username, Email, PassW, Bio)
VALUES ('john_doe', 'john@example.com', 'hashed_password_1', 'Just a regular user.'),
       ('jane_smith', 'jane@example.com', 'hashed_password_2', 'Loves photography and travel.'),
       ('admin_user', 'admin@example.com', 'hashed_password_3', 'Administrator account.');

-- Insert Posts
INSERT INTO Posts (UserID, ContentURL, Caption)
VALUES (1, 'https://example.com/image1.jpg', 'A beautiful sunset!'),
       (2, 'https://example.com/image2.jpg', 'Exploring the mountains.'),
       (1, 'https://example.com/image3.jpg', 'Another day, another adventure.');

-- Insert Comments
INSERT INTO Comments (PostID, UserID, Content)
VALUES (1, 2, 'Amazing photo!'),
       (2, 1, 'Looks like a great place to visit.'),
       (3, 2, 'Nice shot!');

-- Insert Likes
INSERT INTO Likes (PostID, UserID)
VALUES (1, 2),
       (2, 1),
       (3, 2),
       (1, 3);

-- Insert Follows
INSERT INTO Follows (FollowerID, FolloweeID)
VALUES (2, 1), -- Jane follows John
       (1, 2), -- John follows Jane
       (3, 1); -- Admin follows John

-- select *
-- from Users;
-- UPDATE Users
-- SET Bio = 'this is a new Bio!!'
-- WHERE UserID = 12;
-- select *
-- from Users;
-- UPDATE Users
--     SET PassW = 'hashed_password_1'
--     WHERE UserID = 1;
-- UPDATE Users
-- SET PassW = '$2a$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy'
-- WHERE Username = 'john_doe';

-- -- select users who like post with ID 1
-- select u.*
-- from Users u
-- join Likes l on u.UserID = l.UserID
-- where l.PostID = 1;

-- DELETE FROM Posts WHERE UserID = 2;
-- SELECT * from Posts;

-- SELECT * from Posts;
-- delete from Posts where PostID = 5;

-- SELECT p.*, u.Username FROM Posts p
--                                 JOIN Users u ON p.UserID = u.UserID
-- WHERE p.UserID = ?
--    OR p.UserID IN (SELECT FolloweeID FROM Follows WHERE FollowerID = ?)
-- ORDER BY p.CreatedAt DESC LIMIT ?, ?;

-- #
-- # -- Add Verified column to Users table
-- # ALTER TABLE Users
-- #     ADD COLUMN Verified BOOLEAN DEFAULT false;
-- #
-- # -- Create VerificationTokens table
-- # CREATE TABLE VerificationTokens (
-- #                                     TokenID INT AUTO_INCREMENT PRIMARY KEY,
-- #                                     UserID INT NOT NULL,
-- #                                     Token VARCHAR(255) NOT NULL UNIQUE,
-- #                                     ExpiryDate TIMESTAMP NOT NULL,
-- #                                     CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
-- #                                     FOREIGN KEY (UserID) REFERENCES Users(UserID) ON DELETE CASCADE
-- # );
-- #
-- # -- Optional: Create index on Token for faster lookups
-- # CREATE INDEX idx_verification_token ON VerificationTokens(Token);

-- # DROP DATABASE ConPay;
