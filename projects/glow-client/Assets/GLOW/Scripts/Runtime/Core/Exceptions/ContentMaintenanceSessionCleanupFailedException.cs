using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class ContentMaintenanceSessionCleanupFailedException : WrappedServerErrorException
    {
        public ContentMaintenanceSessionCleanupFailedException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}

