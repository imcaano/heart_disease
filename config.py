import os
from dotenv import load_dotenv

load_dotenv()

class Config:
    SECRET_KEY = os.environ.get('SECRET_KEY') or 'your-secret-key-here'
    # XAMPP default: user 'root', no password
    SQLALCHEMY_DATABASE_URI = os.environ.get('DATABASE_URL') or 'mysql://root:@localhost/heart_disease'
    SQLALCHEMY_TRACK_MODIFICATIONS = False
    
    # MetaMask Configuration
    WEB3_PROVIDER_URI = os.environ.get('WEB3_PROVIDER_URI') or 'http://127.0.0.1:8545'
    CONTRACT_ADDRESS = os.environ.get('CONTRACT_ADDRESS')
    CONTRACT_ABI = os.environ.get('CONTRACT_ABI')

# .env example (not used if you set values above):
# SECRET_KEY=your-secret-key-here
# DATABASE_URL=mysql://root:@localhost/heart_disease
# WEB3_PROVIDER_URI=http://127.0.0.1:8545
# CONTRACT_ADDRESS=your_contract_address
# CONTRACT_ABI=your_contract_abi 