using GLOW.Core.Domain.Repositories;
using GLOW.Scenes.Home.Domain.Misc;
using Zenject;

namespace GLOW.Scenes.PartyFormation.Domain.UseCases
{
    public class ApplyUpdatedPartyUseCase
    {
        [Inject] IPartyFormationApplier PartyFormationApplier { get; }
        [Inject] IPartyCacheRepository PartyCacheRepository { get; }
        [Inject] IPreferenceRepository PreferenceRepository { get; }

        public void Apply()
        {
            PartyCacheRepository.ResetBonusUnits();

            PartyFormationApplier.ApplyPartyFormation();
            PreferenceRepository.SelectPartyNo = PartyCacheRepository.GetCurrentPartyModel().PartyNo;
        }
    }
}
