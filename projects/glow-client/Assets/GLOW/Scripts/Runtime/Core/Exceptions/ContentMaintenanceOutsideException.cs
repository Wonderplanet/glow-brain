using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class ContentMaintenanceOutsideException : WrappedServerErrorException
    {
        public ContentMaintenanceOutsideException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
