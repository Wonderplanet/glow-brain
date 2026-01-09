using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class AdminDebugFailedException : WrappedServerErrorException
    {
        public AdminDebugFailedException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
