using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class RequireResourceUpdateException : WrappedServerErrorException
    {
        public RequireResourceUpdateException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
