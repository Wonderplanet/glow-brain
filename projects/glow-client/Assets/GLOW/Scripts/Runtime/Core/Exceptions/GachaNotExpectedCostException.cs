using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class GachaNotExpectedCostException : WrappedServerErrorException
    {
        public GachaNotExpectedCostException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
