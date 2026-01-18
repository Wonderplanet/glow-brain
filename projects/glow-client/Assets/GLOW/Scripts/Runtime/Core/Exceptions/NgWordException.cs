using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class NgWordException : WrappedServerErrorException
    {
        public NgWordException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
