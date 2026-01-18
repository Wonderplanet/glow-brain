namespace GLOW.Core.Data.DataStores.Decryptor
{
    public interface IMasterDataDecryptor
    {
        byte[] Decrypt(byte[] data);
    }
}