using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class CrossDayException : WrappedServerErrorException
    {
        public CrossDayException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}