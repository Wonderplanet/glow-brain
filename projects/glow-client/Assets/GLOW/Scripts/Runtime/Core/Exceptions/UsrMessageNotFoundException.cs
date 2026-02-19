using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class UsrMessageNotFoundException : WrappedServerErrorException
    {
        public UsrMessageNotFoundException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
