using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Modules.CommonWebView.Domain.UseCase
{
    public class GetMyIdUseCase
    {
        [Inject] IGameRepository GameRepository { get; }

        public UserMyId GetMyId()
        {
            return GameRepository.GetGameFetchOther().UserProfileModel.MyId;
        }
    }
}
