using System;
using WPFramework.Constants.MasterData;

namespace GLOW.Core.Exceptions
{
    public class MasterDataDecryptException : ApplicationException
    {
        public MasterDataDecryptException(string path, MasterType masterType, Exception innerException) :
            base($"Failed to decrypt master data. MasterType: {masterType} Path: {path}", innerException)
        {
        }

        public MasterDataDecryptException(MasterType masterType, Exception innerException) :
            base($"Failed to decrypt master data. MasterType: {masterType}", innerException)
        {
        }
    }
}