using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Evaluator;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.HomePartyFormation.Domain.Models;
using WonderPlanet.UnityStandard.Extension;
using Zenject;
namespace GLOW.Scenes.HomePartyFormation.Domain.UseCases
{
    public class HomePartyFormationUseCase
    {
        [Inject] IMstInGameSpecialRuleDataRepository MstInGameSpecialRuleDataRepository { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }

        public HomePartyFormationUseCaseModel CreateHomePartyFormationUseCaseModel(MasterDataId mstStageId, InGameContentType inGameContentType)
        {
            IReadOnlyList<MstInGameSpecialRuleModel> mstInGameSpecialRuleModels;
            if (mstStageId.IsEmpty())
            {
                mstInGameSpecialRuleModels = new List<MstInGameSpecialRuleModel>();
            }
            else
            {
                mstInGameSpecialRuleModels = MstInGameSpecialRuleDataRepository.GetInGameSpecialRuleModels(mstStageId, inGameContentType);
            }
            var useCaseItemModels = CreateUseCaseItemModels(GameRepository.GetGameFetchOther().UserUnitModels, mstInGameSpecialRuleModels);
            return new HomePartyFormationUseCaseModel(useCaseItemModels);
        }

        List<HomePartyFormationUseCaseSpecialRuleUnitItemModel> CreateUseCaseItemModels(
            IReadOnlyList<UserUnitModel> userUnitModels,
            IReadOnlyList<MstInGameSpecialRuleModel> mstInGameSpecialRuleModels)
        {
            var result = new List<HomePartyFormationUseCaseSpecialRuleUnitItemModel>();
            foreach (var userUnitModel in userUnitModels)
            {
                var mstCharacter = MstCharacterDataRepository.GetCharacter(userUnitModel.MstUnitId);
                var achievedSpecialRuleFlag = new InGameSpecialRuleAchievedFlag(InGameSpecialRuleAchievingEvaluator.IsAchievedSpecialRule(
                    mstCharacter,
                    mstInGameSpecialRuleModels));
                result.Add(new HomePartyFormationUseCaseSpecialRuleUnitItemModel(
                    userUnitModel.UsrUnitId,
                    achievedSpecialRuleFlag));
            }
            return result;
        }
    }
}
