using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class BoxGachaCostNotEnoughException : WrappedServerErrorException
    {
        public BoxGachaCostNotEnoughException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}