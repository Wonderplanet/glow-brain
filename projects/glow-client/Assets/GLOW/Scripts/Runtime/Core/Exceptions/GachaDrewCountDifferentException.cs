using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class GachaDrewCountDifferentException : DataInconsistencyServerErrorException
    {
        public GachaDrewCountDifferentException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
