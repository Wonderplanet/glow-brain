using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class GachaNotExpectedPlayNumException : WrappedServerErrorException
    {
        public GachaNotExpectedPlayNumException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
