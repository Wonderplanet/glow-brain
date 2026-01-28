using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class EncyclopediaNotReachedEncyclopediaRankException : DataInconsistencyServerErrorException
    {
        public EncyclopediaNotReachedEncyclopediaRankException(ServerErrorException serverErrorException) 
            : base(serverErrorException)
        {
        }
    }
}
