using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Helper;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Pvp;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Extensions;
using GLOW.Scenes.InGame.Domain.Battle.Calculator;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.Battle.InGameInitializers
{
    public class RushInitializer : IRushInitializer
    {
        static readonly RushChargeCount DefaultMaxChargeCount = new (3);

        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstConfigRepository MstConfigRepository { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] IPvpSelectedOpponentStatusCacheRepository PvpSelectedOpponentStatusCacheRepository { get; }
        [Inject] IUnitStatusCalculateHelper UnitStatusCalculateHelper { get; }
        [Inject] IRushChargeTimeCalculator RushChargeTimeCalculator { get; }

        public RushInitializerResult Initialize(
            QuestType questType,
            IReadOnlyList<DeckUnitModel> deckUnits,
            IReadOnlyList<DeckUnitModel> pvpOpponentDeckUnits,
            OutpostEnhancementModel outpostEnhancement,
            OutpostEnhancementModel pvpOpponentOutpostEnhancement)
        {

            // プレイヤー側
            var specialUnitBonus = GetSpecialUnitBonus(deckUnits);
            var rushModel = InitializeRushModel(
                questType,
                outpostEnhancement,
                specialUnitBonus,
                BattleSide.Player);

            // Pvpの対戦相手側
            var pvpOpponentRushModel = RushModel.Empty;
            var opponentStatus = PvpSelectedOpponentStatusCacheRepository.GetOpponentStatus();
            if (!opponentStatus.IsEmpty())
            {
                var opponentSpecialUnitBonus =
                    GetSpecialUnitBonus(pvpOpponentDeckUnits, opponentStatus.PvpUnits);

                pvpOpponentRushModel = InitializeRushModel(
                    questType,
                    pvpOpponentOutpostEnhancement,
                    opponentSpecialUnitBonus,
                    BattleSide.Enemy);
            }

            return new RushInitializerResult(
                rushModel,
                pvpOpponentRushModel);
        }

        /// <summary> プレイヤー用のスペシャルユニットボーナスの取得 </summary>
        PercentageM GetSpecialUnitBonus(IReadOnlyList<DeckUnitModel> deckUnits)
        {
            var gameFetchOther = GameRepository.GetGameFetchOther();
            var userUnits = gameFetchOther.UserUnitModels;

            // 編成されているスペシャルキャラの取得
            var specialUnitList = deckUnits
                .Where(unit => unit.RoleType == CharacterUnitRoleType.Special)
                .ToList();
            var totalSpecialUnitBonus = PercentageM.Zero;
            foreach (var specialUnit in specialUnitList)
            {
                var userUnit = userUnits.Find(unit => unit.UsrUnitId == specialUnit.UserUnitId);
                var mstUnit = MstCharacterDataRepository.GetCharacter(userUnit.MstUnitId);
                var calculateStatus = UnitStatusCalculateHelper.Calculate(mstUnit, userUnit.Level, userUnit.Rank, userUnit.Grade);

                // PercentageMにキャストする
                var rushBonusPercentage = calculateStatus.AttackPower.ToRushPercentageM();
                totalSpecialUnitBonus += rushBonusPercentage;
            }

            return totalSpecialUnitBonus;
        }

        /// <summary> Pvpの対戦相手用のスペシャルユニットボーナスの取得 </summary>
        PercentageM GetSpecialUnitBonus(
            IReadOnlyList<DeckUnitModel> deckUnits,
            IReadOnlyList<PvpUnitModel> pvpUnits)
        {
            // 編成されているスペシャルキャラの取得
            var specialUnitList = deckUnits
                .Where(unit => unit.RoleType == CharacterUnitRoleType.Special)
                .ToList();
            var totalSpecialUnitBonus = PercentageM.Zero;
            foreach (var specialUnit in specialUnitList)
            {
                var pvpUnit = pvpUnits.Find(unit => unit.MstUnitId == specialUnit.CharacterId);
                var mstUnit = MstCharacterDataRepository.GetCharacter(pvpUnit.MstUnitId);
                var calculateStatus = UnitStatusCalculateHelper.Calculate(
                    mstUnit,
                    pvpUnit.UnitLevel,
                    pvpUnit.UnitRank,
                    pvpUnit.UnitGrade);

                // PercentageMにキャストする
                var rushBonusPercentage = calculateStatus.AttackPower.ToRushPercentageM();
                totalSpecialUnitBonus += rushBonusPercentage;
            }

            return totalSpecialUnitBonus;
        }

        RushModel InitializeRushModel(
            QuestType questType,
            OutpostEnhancementModel outpostEnhancement,
            PercentageM totalSpecialUnitBonus,
            BattleSide battleSide)
        {
            var chargeTime = RushChargeTimeCalculator.Calculate(outpostEnhancement, MstConfigRepository);

            // 係数
            var rushCoefficient = MstConfigRepository.GetConfig(MstConfigKey.RushDamageCoefficient).Value.ToRushCoefficient();
            // 最大ダメージ上限
            var damageUpper = new AttackPower(MstConfigRepository.GetConfig(MstConfigKey.RushMaxDamage).Value.ToDecimal());

            var attackRange = new AttackRange(
                AttackRangePointType.Page,
                new AttackRangeParameter(0f),
                AttackRangePointType.Page,
                new AttackRangeParameter(0f));

            var attackBaseData = new AttackBaseData(
                Array.Empty<CharacterColor>(),
                KillerPercentage.Hundred,
                TickCount.Empty,
                TickCount.Empty);

            var attackHitData = AttackHitData.KnockBack2 with
            {
                AttackHitBattleEffectId = new AttackHitBattleEffectId(BattleEffectId.RushHit)
            };

            var attackTargetType = questType == QuestType.Enhance
                ? AttackTargetType.All
                : AttackTargetType.Character;

            var attackElement = new AttackElement(
                MasterDataId.Empty,
                new TickCount(0),
                new TickCount(2), // 総攻撃発動直後にヒットエフェクトを表示したくないため2フレーム待つ
                AttackType.Direct,
                attackRange,
                FieldObjectCount.Infinity,
                new AttackViewId(1),
                AttackTarget.Foe,
                attackTargetType,
                (CharacterColor[])Enum.GetValues(typeof(CharacterColor)),
                (CharacterUnitRoleType[])Enum.GetValues(typeof(CharacterUnitRoleType)),
                AttackDamageType.RushDamage,
                attackHitData,
                AttackHitStopFlag.False,
                Percentage.Hundred,
                new AttackPowerParameter(AttackPowerParameterType.Percentage, 100),
                StateEffect.Empty,
                Array.Empty<AttackSubElement>());

            var attackElements = new List<AttackElement> { attackElement };
            var attackData = new AttackData(
                TickCount.Empty,
                attackBaseData,
                attackElements);

            var rushChargeBonusModels = new List<RushChargeModel>
            {
                new RushChargeModel(new RushChargeCount(1),
                    MstConfigRepository.GetConfig(MstConfigKey.RushGaugeChargeFirst).Value.ToPercentageM(),
                    MstConfigRepository.GetConfig(MstConfigKey.RushKnockBackTypeFirst).Value.ToAttackHitType()),
                new RushChargeModel(new RushChargeCount(2),
                    MstConfigRepository.GetConfig(MstConfigKey.RushGaugeChargeSecond).Value.ToPercentageM(),
                    MstConfigRepository.GetConfig(MstConfigKey.RushKnockBackTypeSecond).Value.ToAttackHitType()),
                new RushChargeModel(new RushChargeCount(3),
                    MstConfigRepository.GetConfig(MstConfigKey.RushGaugeChargeThird).Value.ToPercentageM(),
                    MstConfigRepository.GetConfig(MstConfigKey.RushKnockBackTypeThird).Value.ToAttackHitType()),
            };

            var rushModel = new RushModel(
                chargeTime,
                chargeTime,
                RushChargeCount.Zero,
                DefaultMaxChargeCount,
                CanExecuteRushFlag.False,
                ExecuteRushFlag.False,
                rushChargeBonusModels,
                totalSpecialUnitBonus,
                RushPowerUpStateEffectBonus.Zero,
                attackData,
                rushCoefficient,
                damageUpper);

            return rushModel;
        }
    }
}
