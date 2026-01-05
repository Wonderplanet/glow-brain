using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class GachaNotExpectedResourceTypeException : WrappedServerErrorException
    {
        public GachaNotExpectedResourceTypeException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
