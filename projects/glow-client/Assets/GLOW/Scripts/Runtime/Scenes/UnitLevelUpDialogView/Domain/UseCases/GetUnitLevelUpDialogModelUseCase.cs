using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Encoder;
using GLOW.Core.Domain.Helper;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Unit;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.UnitLevelUpDialogView.Domain.Models;
using GLOW.Scenes.UnitLevelUpDialogView.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.UnitLevelUpDialogView.Domain.UseCases
{
    public class GetUnitLevelUpDialogModelUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] IMstUnitLevelUpRepository MstUnitLevelUpRepository { get; }
        [Inject] IMstUnitRankUpRepository MstUnitRankUpRepository { get; }
        [Inject] IMstUnitSpecificRankUpRepository MstUnitSpecificRankUpRepository { get; }
        [Inject] IMstConfigRepository MstConfigRepository { get; }
        [Inject] IUnitStatusCalculateHelper UnitStatusCalculateHelper { get; }
        [Inject] ISpecialAttackDescriptionEncoder SpecialAttackDescriptionEncoder { get; }
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }
        [Inject] ISpecialAttackInfoModelFactory SpecialAttackInfoModelFactory { get; }

        public UnitLevelUpDialogModel GetLevelUpModel(UserDataId userUnitId)
        {
            var coin = GameRepository.GetGameFetch().UserParameterModel.Coin;
            var userUnit = GameRepository.GetGameFetchOther().UserUnitModels.Find(unit => unit.UsrUnitId == userUnitId);
            var mstUnit = MstCharacterDataRepository.GetCharacter(userUnit.MstUnitId);

            // 次のランクがある場合はその要求レベルまで、ない場合は最大レベルまで
            var enableLevelUps = GetEnableLevelUps(mstUnit, userUnit);

            var currentCalculateStatus = UnitStatusCalculateHelper
                .Calculate(mstUnit, userUnit.Level, userUnit.Rank, userUnit.Grade);
            var maxLevel = MstUnitLevelUpRepository.GetUnitMaxLevelUp(mstUnit.UnitLabel);

            var levelValues = new List<UnitLevelUpValueModel>();
            var consumedCoin = coin;
            var consumeCoin = Coin.Zero;
            for(int i = 0 ; i < enableLevelUps.Count ; ++i)
            {
                var model = enableLevelUps[i];
                consumedCoin -= model.RequiredCoin;
                consumeCoin += model.RequiredCoin;
                var enableMinimum = i != 0;
                var enableMinus = enableMinimum;
                var enableMaximum = i != enableLevelUps.Count - 1;
                var enablePlus = enableMaximum;
                // 最大レベルじゃない場合、次のレベルアップに必要なコインを消費しても残りが0以上なら最大ボタン有効化
                if (enableMaximum)
                {
                    var nextConsumedCoin = consumedCoin - enableLevelUps[i + 1].RequiredCoin;
                    enableMaximum = nextConsumedCoin >= Coin.Zero;
                }
                var calculateStatus = UnitStatusCalculateHelper.Calculate(mstUnit, model.Level, userUnit.Rank, userUnit.Grade);

                var specialAttackInfoModel = SpecialAttackInfoModelFactory.Create(mstUnit, userUnit.Grade, model.Level);

                var valueModel = TranslateLevelUpValueModel(
                    model,
                    consumeCoin,
                    consumedCoin,
                    enableMinimum,
                    enableMaximum,
                    enableMinus,
                    enablePlus,
                    calculateStatus,
                    specialAttackInfoModel.Name,
                    specialAttackInfoModel.Description);

                levelValues.Add(valueModel);
            }

            var iconModel = PlayerResourceModelFactory.Create(
                ResourceType.Coin, MasterDataId.Empty, new PlayerResourceAmount(0));

            return new UnitLevelUpDialogModel(
                mstUnit.RoleType,
                iconModel,
                userUnit.Level,
                coin,
                currentCalculateStatus.HP,
                currentCalculateStatus.AttackPower,
                levelValues);
        }

        IReadOnlyList<MstUnitLevelUpModel> GetEnableLevelUps(MstCharacterModel mstUnit, UserUnitModel userUnit)
        {
            var levelCap = MstConfigRepository.GetConfig(MstConfigKey.UnitLevelCap).ToUnitLevel();
            var nextRankLevel = UnitLevel.Empty;
            if (mstUnit.HasSpecificRankUp)
            {
                var specificRankUp = MstUnitSpecificRankUpRepository.GetUnitSpecificRankUpList(mstUnit.Id)
                    .FirstOrDefault(rank => rank.Rank.Value == userUnit.Rank.Value + 1, MstUnitSpecificRankUpModel.Empty);
                nextRankLevel = specificRankUp.RequireLevel;
            }
            else
            {
                var nextRank = MstUnitRankUpRepository.GetUnitRankUpList(mstUnit.UnitLabel)
                    .FirstOrDefault(rank => rank.Rank.Value == userUnit.Rank.Value + 1, MstUnitRankUpModel.Empty);
                nextRankLevel = nextRank.RequireLevel;
            }

            var mstUnitLevelUps = MstUnitLevelUpRepository.GetUnitLevelUpList(mstUnit.UnitLabel);
            var levelUps = mstUnitLevelUps
                .Where(level => userUnit.Level < level.Level && level.Level <= levelCap);

            if (!nextRankLevel.IsEmpty())
            {
                levelUps = levelUps.Where(level => level.Level <= nextRankLevel);
            }
            return levelUps
                .OrderBy(level => level.Level)
                .ToList();
        }

        UnitLevelUpValueModel TranslateLevelUpValueModel(
            MstUnitLevelUpModel model,
            Coin consumeCoinValue,
            Coin consumedCoin,
            bool enableMinimum,
            bool enableMaximum,
            bool enableMinus,
            bool enablePlus,
            UnitCalculateStatusModel status,
            SpecialAttackName specialAttackName,
            SpecialAttackInfoDescription specialAttackDescription)
        {
            var buttonState = new LevelUpButtonState(
                enableMinimum, enableMaximum,
                enableMinus, enablePlus,
                consumedCoin >= Coin.Zero);
            return new UnitLevelUpValueModel(
                model.Level,
                consumeCoinValue,
                consumedCoin,
                status.HP,
                status.AttackPower,
                specialAttackName,
                specialAttackDescription,
                buttonState);
        }
    }
}
