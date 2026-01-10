using GLOW.Core.Domain.Models;
using GLOW.Scenes.Home.Domain.Models;

namespace GLOW.Scenes.Home.Domain.UseCases
{
    public interface IHomeUseCases
    {
        HomeUserParameterUseCaseModel GetUserParameter();
        UserProfileModel GetUserProfile();
    }
}
