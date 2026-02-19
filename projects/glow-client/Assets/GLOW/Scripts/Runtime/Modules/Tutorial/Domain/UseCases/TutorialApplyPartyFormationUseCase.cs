using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using Zenject;

namespace GLOW.Modules.Tutorial.Domain.UseCases
{
    public class TutorialApplyPartyFormationUseCase
    {
        [Inject] IPartyCacheRepository PartyCacheRepository { get; }
        [Inject] IGameRepository GameRepository { get; }

        public IReadOnlyList<UserPartyCacheModel> GetNeedApplyPartyFormation()
        {
            var originalParties = GameRepository.GetGameFetchOther().UserPartyModels;
            return PartyCacheRepository.GetNeedsApplyParty(originalParties);
        }
    }
}