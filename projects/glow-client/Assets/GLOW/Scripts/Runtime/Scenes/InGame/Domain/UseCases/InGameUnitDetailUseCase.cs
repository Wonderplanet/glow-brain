using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Helper;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Modules.Tutorial.Domain.Definitions;
using GLOW.Scenes.InGame.Domain.Battle;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models.InGameUnitDetail;
using GLOW.Scenes.PartyFormation.Domain.Evaluator;
using GLOW.Scenes.UnitEnhance.Domain.ModelFactories;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.UseCases
{
    public class InGameUnitDetailUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] IUnitStatusCalculateHelper UnitStatusCalculateHelper { get; }
        [Inject] IInGameScene InGameScene { get; }
        [Inject(Id = InGameUnitEncyclopediaBindIds.Player)]
        IInGameUnitEncyclopediaEffectProvider UnitEncyclopediaEffectProvider { get; }
        [Inject] IInGameEventBonusUnitEffectProvider InGameEventBonusUnitEffectProvider { get; }
        [Inject] IUnitEnhanceAbilityModelListFactory UnitEnhanceAbilityModelListFactory { get; }
        [Inject] ISpecialAttackInfoModelFactory SpecialAttackInfoModelFactory { get; }
        [Inject] IInGameUnitStatusCalculator InGameUnitStatusCalculator { get; }
        [Inject] IInGameSpecialRuleUnitStatusEvaluator InGameSpecialRuleUnitStatusEvaluator { get; }

        public InGameUnitDetailModel GetInGameUnitDetail(UserDataId userUnitId)
        {
            var userUnit = GetUserUnit(userUnitId);

            var mstUnit = MstCharacterDataRepository.GetCharacter(userUnit.MstUnitId);

            return new InGameUnitDetailModel(
                CreateInfoModel(mstUnit, userUnit),
                CreateSpecialAttackModel(mstUnit, userUnit),
                UnitEnhanceAbilityModelListFactory.Create(mstUnit, userUnit.Rank),
                CreateStatusModel(mstUnit, userUnit));
        }

        UserUnitModel GetUserUnit(UserDataId userUnitId)
        {
            // 初回チュートリアル中のみユニット情報を上書きする
            if (GameRepository.GetGameFetchOther().TutorialStatus.IsIntroduction())
            {
                var mstUnitId = TutorialDefinitionIds.TutorialUnitIds.FirstOrDefault(
                    unit => unit.UserUnitId == userUnitId).MstUnitId;
                return new UserUnitModel(
                    mstUnitId,
                    userUnitId,
                    new UnitLevel(80),
                    new UnitRank(5),
                    new UnitGrade(5),
                    NewEncyclopediaFlag.False);
            }
            else
            {
                return GameRepository.GetGameFetchOther().UserUnitModels.Find(unit => unit.UsrUnitId == userUnitId);
            }
        }

        InGameUnitDetailInfoModel CreateInfoModel(MstCharacterModel mstUnit, UserUnitModel userUnit)
        {
            var calculateStatus = UnitStatusCalculateHelper.Calculate(mstUnit, userUnit.Level, userUnit.Rank, userUnit.Grade);
            var unitIcon = CharacterIconModelFactory.CreateLSize(mstUnit, userUnit, calculateStatus);
            var fieldUnit = InGameScene.CharacterUnits.FirstOrDefault(unit => unit.CharacterId == mstUnit.Id);
            var effectList = new List<StateEffectType>();
            if (fieldUnit != null)
            {
                foreach (var effect in fieldUnit.StateEffects)
                {
                    if (!effect.NeedsDisplay) continue;

                    effectList.Add(effect.Type);
                }
            }

            EventBonusPercentage eventBonus;
            if (InGameScene.Type == InGameType.AdventBattle)
            {
                eventBonus = InGameEventBonusUnitEffectProvider.GetUnitEventBonus(mstUnit.Id, InGameScene.EventBonusGroupId);
            }
            else
            {
                eventBonus = InGameEventBonusUnitEffectProvider.GetUnitEventBonus(mstUnit.Id, InGameScene.MstQuest.Id);
            }

            return new InGameUnitDetailInfoModel(
                unitIcon,
                mstUnit.Name,
                effectList,
                eventBonus);
        }

        InGameUnitDetailSpecialAttackModel CreateSpecialAttackModel(MstCharacterModel mstUnit, UserUnitModel userUnit)
        {
            var specialAttackInfoModel = SpecialAttackInfoModelFactory.Create(mstUnit, userUnit);

            return new InGameUnitDetailSpecialAttackModel(
                specialAttackInfoModel.Name,
                specialAttackInfoModel.Description,
                specialAttackInfoModel.CoolTime);
        }

        InGameUnitDetailStatusModel CreateStatusModel(MstCharacterModel mstUnit, UserUnitModel userUnit)
        {
            var calculateStatus = UnitStatusCalculateHelper.Calculate(mstUnit, userUnit.Level, userUnit.Rank, userUnit.Grade);
            var fieldUnit = InGameScene.CharacterUnits.FirstOrDefault(unit => unit.CharacterId == mstUnit.Id);
            var unitSpeed = mstUnit.UnitMoveSpeed;

            var statusWithBonus = InGameUnitStatusCalculator.CalculateStatus(
                calculateStatus,
                mstUnit.Id,
                UnitEncyclopediaEffectProvider,
                InGameScene.Type,
                InGameScene.MstQuest.Id,
                InGameScene.EventBonusGroupId,
                InGameScene.SpecialRuleUnitStatusModels);

            var eventBonus = PercentageM.Hundred;
            if (InGameScene.Type == InGameType.AdventBattle)
            {
                eventBonus += InGameEventBonusUnitEffectProvider
                    .GetUnitEventBonus(mstUnit.Id, InGameScene.EventBonusGroupId).ToPercentageM();
            }
            else
            {
                eventBonus += InGameEventBonusUnitEffectProvider
                    .GetUnitEventBonus(mstUnit.Id, InGameScene.MstQuest.Id).ToPercentageM();
            }

            var hp = statusWithBonus.HP;
            var calculateAttackPower = statusWithBonus.AttackPower;
            var attackPower = calculateAttackPower;
            
            // 初期値は現在HPを最大HPと同じにする(未出撃の場合は最大HPを表示したいため)
            var currentHp = hp;

            if (fieldUnit != null)
            {
                // 攻撃力計算
                var buffs = fieldUnit.StateEffects;
                attackPower = InGameUnitStatusCalculator.CalculateBuffAttackPower(attackPower, buffs);

                // 移動速度計算
                unitSpeed = InGameUnitStatusCalculator.CalculateBuffUnitMoveSpeed(unitSpeed, buffs);
                currentHp = fieldUnit.Hp;
            }

            // 図鑑効果適用後のステータスを取得 これを各種ステータス上昇適用前の数値として扱う
            var unitStatusWithEncyclopediaEffect = InGameUnitStatusCalculator.CalculateStatusWithEncyclopediaEffect(
                calculateStatus,
                UnitEncyclopediaEffectProvider);

            var balloonMessageList = new List<InGameUnitDetailBalloonMessage>();
            // 特殊ルールによるステータス上昇が存在するか
            if (InGameSpecialRuleUnitStatusEvaluator.EvaluateTarget(mstUnit, InGameScene.SpecialRuleUnitStatusModels))
            {
                balloonMessageList.Add(new InGameUnitDetailBalloonMessage("特別ルール対象 ステータスUP中!!"));
            }
            // イベントボーナスによるステータス上昇が存在するか
            if (eventBonus > PercentageM.Hundred)
            {
                balloonMessageList.Add(new InGameUnitDetailBalloonMessage("ボーナス対象 ステータスUP中!!"));
            }

            return new InGameUnitDetailStatusModel(
                mstUnit.RoleType,
                hp,
                currentHp,
                unitStatusWithEncyclopediaEffect.HP,
                attackPower,
                unitStatusWithEncyclopediaEffect.AttackPower,
                mstUnit.AttackRangeType,
                unitSpeed,
                mstUnit.UnitMoveSpeed,
                balloonMessageList);
        }
    }
}
