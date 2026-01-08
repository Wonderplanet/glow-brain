using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class AvailableAssetVersionNotFoundException : WrappedServerErrorException
    {
        public AvailableAssetVersionNotFoundException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
