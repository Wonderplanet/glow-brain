using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class ContentMaintenanceException : WrappedServerErrorException
    {
        public ContentMaintenanceException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
