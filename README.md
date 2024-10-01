# Symfony Ledger - a simple blockchain API

This project is a self-contained API built with Symfony that allows you to create a simple blockchain. Each block in the blockchain consists of several attributes, ensuring a structured and secure way to manage actions and their associated data.

## Block Model

Each block in the blockchain has the following attributes:

- **id**: A unique identifier for the block.
- **identifier**: A human-readable unique name for the block.
- **action**: The name of the action associated with this block.
- **date**: The date when the block was created.
- **metadata**: Additional information related to the block.
- **signature**: Each block's signature is calculated using the signature of the previous block, ensuring the integrity of the blockchain.

## Database Requirements

This project requires a database to store the blockchain data. You can use any database supported by Doctrine, such as:

- MySQL
- PostgreSQL
- SQLite
- MariaDB
- Oracle

Make sure to set up your database and configure the connection parameters in your `.env` file.

## Environment Variables

To configure the API, you need to set the following environment variables, in addition to other Symfony environment variables:

- **API_KEY**: This key is required to access the API.
- **BLOCKCHAIN_PRIVATE_KEY**: This private key is used for signing blocks in the blockchain.