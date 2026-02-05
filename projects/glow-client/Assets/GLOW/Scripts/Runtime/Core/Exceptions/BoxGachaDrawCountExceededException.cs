using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class BoxGachaDrawCountExceededException : WrappedServerErrorException
    {
        public BoxGachaDrawCountExceededException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}