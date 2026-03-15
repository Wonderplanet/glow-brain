using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.Home.Domain.UseCases
{
    public class SaveCurrentMstKomaPatternUseCase
    {
        [Inject] IHomeMainKomaSettingUserRepository UserRepository { get; }

        public void Execute(MasterDataId mstHomeKomaPatternId)
        {
            UserRepository.SetCurrentMstHomeKomaPatternId(mstHomeKomaPatternId);
        }
    }
}