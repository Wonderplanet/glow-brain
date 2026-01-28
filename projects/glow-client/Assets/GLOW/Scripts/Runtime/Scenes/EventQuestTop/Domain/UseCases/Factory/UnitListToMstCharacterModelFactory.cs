using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using Zenject;

namespace GLOW.Scenes.EventQuestTop.Domain.UseCases
{
    public class UnitListToMstCharacterModelFactory : IUnitListToMstCharacterModelFactory
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IPartyCacheRepository PartyCacheRepository { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }

        IReadOnlyList<MstCharacterModel> IUnitListToMstCharacterModelFactory.CreateFromCurrentParty()
        {
            var userUnitModels = GameRepository.GetGameFetchOther().UserUnitModels;
            return PartyCacheRepository.GetCurrentPartyModel().GetUnitList()
                .Join(userUnitModels, id => id, userUnit => userUnit.UsrUnitId, (_, userUnit) => userUnit)
                .Select(userUnit => MstCharacterDataRepository.GetCharacter(userUnit.MstUnitId))
                .ToList();
        }
    }
}