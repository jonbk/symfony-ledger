# Symfony Ledger - A Simple Blockchain API

This project is a self-contained API built with Symfony that allows you to create a simple blockchain. Each block in the blockchain consists of several attributes, ensuring a structured and secure way to manage actions and their associated data.

## Block Model

Each block in the blockchain has the following attributes:

- **uuid**: A unique identifier for the block.
- **timestamp**: The timestamp when the block was created.
- **identifier**: A human-readable unique name for the block.
- **author**: The author of the action associated with this block.
- **action**: The name of the action associated with this block.
- **date**: The date when the action was made.
- **metadata**: Additional information related to the block.
- **signature**: Each block's signature is calculated using the signature of the previous block, ensuring the integrity of the blockchain.
- **previousSignature**: The signature of the previous block in the blockchain.

## Database Requirements

This project requires a storage solution to hold the blockchain data. You will use PostgreSQL as your database solution.
Ensure that your AWS environment is configured to assume the necessary IAM role that has permissions to access database.

## Environment Variables

To configure the API, you need to set the following environment variables, in addition to other Symfony environment variables:

- **LEDGER_API_KEY**: This key is required to access the API.
- **LEDGER_SIGNATURE_PRIVATE_KEY**: This private key is used for signing blocks in the blockchain.
- **LEDGER_SIGNATURE_PUBLIC_KEY**: This public key is used for verify blocks in the blockchain.

### Example `.env` Configuration

```dotenv
###> jonbk/symfony-ledger ###
LEDGER_API_KEY=your_api_key
LEDGER_SIGNATURE_PUBLIC_KEY=your_public_key
LEDGER_SIGNATURE_PRIVATE_KEY=your_private_key
###< jonbk/symfony-ledger ###
```

## Usage

Once you have configured your environment, you can start interacting with the API to create and manage blocks in your blockchain.

Make sure to securely manage your private key and implement checks to ensure the integrity of your blockchain when retrieving blocks.