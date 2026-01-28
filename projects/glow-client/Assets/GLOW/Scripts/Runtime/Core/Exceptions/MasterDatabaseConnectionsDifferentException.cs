using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class MasterDatabaseConnectionsDifferentException : WrappedServerErrorException
    {
        public MasterDatabaseConnectionsDifferentException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
