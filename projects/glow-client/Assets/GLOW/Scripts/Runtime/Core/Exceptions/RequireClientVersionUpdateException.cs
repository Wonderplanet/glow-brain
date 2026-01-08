using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class RequireClientVersionUpdateException : WrappedServerErrorException
    {
        public RequireClientVersionUpdateException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
