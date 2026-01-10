using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class MaintenanceException : WrappedServerErrorException
    {
        public MaintenanceException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
