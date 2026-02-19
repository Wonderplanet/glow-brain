using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.OutpostEnhance;
using GLOW.Scenes.OutpostEnhanceLevelUpDialog.Domain.Models;
using GLOW.Scenes.OutpostEnhanceLevelUpDialog.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.OutpostEnhanceLevelUpDialog.Domain.UseCases
{
    public class GetOutpostEnhanceLevelUpDialogModelUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstOutpostEnhanceDataRepository MstOutpostEnhanceDataRepository { get; }

        public OutpostEnhanceLevelUpDialogModel GetLevelUpModel(MasterDataId mstEnhanceId)
        {
            // userDataからの情報
            var userCoin = GameRepository.GetGameFetch().UserParameterModel.Coin;
            var userOutpostList = GameRepository.GetGameFetchOther().UserOutpostModels;
            var userOutpostEnhanceList = GameRepository.GetGameFetchOther().UserOutpostEnhanceModels;
            // 利用している拠点ID
            var usedOutpostId = userOutpostList.FirstOrDefault(userOutpost => userOutpost.IsUsed)?.MstOutpostId ??
                                new MasterDataId(OutpostDefaultParameterConst.DefaultOutpostId);
            // mstDataからの情報
            var outpostModel = MstOutpostEnhanceDataRepository.GetOutpostModel(usedOutpostId);

            var enhancementModel = outpostModel.EnhancementModels.FirstOrDefault(enhancement => enhancement.Id == mstEnhanceId);
            if (enhancementModel == null) return null;

            var currentLevel = userOutpostEnhanceList.FirstOrDefault(userOutpostEnhance => userOutpostEnhance.MstOutpostEnhanceId == mstEnhanceId)?.Level ??
                               new OutpostEnhanceLevel(1);
            var levels = enhancementModel.Levels;
            var enableLevelUps = levels.Where(level => level.Level > currentLevel);
            var orderedEnableLevelUps = enableLevelUps.OrderBy(level => level.Level).ToList();

            var levelValues = new List<OutpostEnhanceLevelUpValueModel>();
            var consumedCoin = userCoin;
            var requiredCoin = Coin.Zero;

            for (int i = 0; i < orderedEnableLevelUps.Count; i++)
            {
                var model = orderedEnableLevelUps[i];
                consumedCoin -= model.Cost;
                requiredCoin += model.Cost;
                var enableMinimum = i != 0;
                var enableMinus = enableMinimum;
                var enableMaximum = i != orderedEnableLevelUps.Count - 1;
                var enablePlus = enableMaximum;
                if (enableMaximum)
                {
                    var nextConsumedCoin = consumedCoin - orderedEnableLevelUps[i + 1].Cost;
                    enableMaximum = nextConsumedCoin >= Coin.Zero;
                }

                var valueModel = TranslateLevelUpValueModel(model.Level, requiredCoin, consumedCoin,
                    enableMinimum, enableMaximum, enableMinus, enablePlus);
                levelValues.Add(valueModel);
            }
            return new OutpostEnhanceLevelUpDialogModel(currentLevel, userCoin, levelValues);
        }

        OutpostEnhanceLevelUpValueModel TranslateLevelUpValueModel(
            OutpostEnhanceLevel level,
            Coin requiredCoin,
            Coin consumedCoin,
            bool enableMinimum,
            bool enableMaximum,
            bool enableMinus,
            bool enablePlus)
        {
            var buttonState = new OutpostEnhanceLevelUpButtonState(
                enableMinimum, enableMaximum,
                enableMinus, enablePlus,
                consumedCoin >= Coin.Zero);
            return new OutpostEnhanceLevelUpValueModel(level, requiredCoin, consumedCoin, buttonState);
        }
    }
}
