using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Factories;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.Home.Domain.Models;
using Zenject;

namespace GLOW.Scenes.Home.Domain.UseCases
{
    public class StageLimitStatusUseCase
    {
        [Inject] IPartyCacheRepository PartyCacheRepository { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] IStageLimitStatusModelFactory StageLimitStatusModelFactory { get; }

        public InGameSpecialRuleStatusModel GetStageLimitStatusModel(MasterDataId mstStageId, bool isInvalidOnly)
        {
            // 選択中パーティ取得
            var currentParty = PartyCacheRepository.GetCurrentPartyModel();
            var calcTargetMstCharacterModels = currentParty.GetUnitList()
                .Where(c => !c.IsEmpty())
                .Select(id =>
                {
                    var userUnit = GameRepository.GetGameFetchOther().UserUnitModels.Find(model => model.UsrUnitId.Value == id.Value);
                    return MstCharacterDataRepository.GetCharacter(userUnit.MstUnitId);
                })
                .ToList();

            if (isInvalidOnly)
            {
                return StageLimitStatusModelFactory.CreateInvalidStageLimitStatusModel(
                    mstStageId,
                    InGameContentType.Stage,
                    currentParty.PartyName,
                    calcTargetMstCharacterModels);
            }
            else
            {
                return StageLimitStatusModelFactory.CreateStageLimitStatusModel(
                    mstStageId,
                    InGameContentType.Stage,
                    currentParty.PartyName);
            }
        }
    }
}
