using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class ContentMaintenanceNeedCleanupException : WrappedServerErrorException
    {
        public ContentMaintenanceNeedCleanupException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
