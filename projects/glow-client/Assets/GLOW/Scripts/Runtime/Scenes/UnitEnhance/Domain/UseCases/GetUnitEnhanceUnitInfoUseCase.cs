using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Helper;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.UnitEnhance.Domain.ModelFactories;
using GLOW.Scenes.UnitEnhance.Domain.Models;
using WonderPlanet.UnityStandard.Extension;
using Zenject;

namespace GLOW.Scenes.UnitEnhance.Domain.UseCases
{
    public class GetUnitEnhanceUnitInfoUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject]  IMstUnitRankUpRepository MstUnitRankUpRepository { get; }
        [Inject] IMstConfigRepository MstConfigRepository { get; }
        [Inject] IUnitStatusCalculateHelper UnitStatusCalculateHelper { get; }
        [Inject] IUnitEnhanceAbilityModelListFactory UnitEnhanceAbilityModelListFactory { get; }
        [Inject] ISpecialAttackInfoModelFactory SpecialAttackInfoModelFactory { get; }
        [Inject] IMstUnitGradeUpRewardRepository MstUnitGradeUpRewardRepository { get; }

        public UnitEnhanceUnitInfoModel GetUnitInfo(UserDataId userUnitId)
        {
            var userUnit = GameRepository.GetGameFetchOther().UserUnitModels.Find(unit => unit.UsrUnitId == userUnitId);
            var mstUnit = MstCharacterDataRepository.GetCharacter(userUnit.MstUnitId);
            var nextRank = MstUnitRankUpRepository.GetUnitRankUpList(mstUnit.UnitLabel)
                .Where(rank => userUnit.Rank < rank.Rank)
                .OrderBy(rank => rank.Rank)
                .FirstOrDefault();
            var characterImagePath = UnitImageAssetPath.FromAssetKey(mstUnit.AssetKey);
            var unitLevelCap = new UnitLevel(MstConfigRepository.GetConfig(MstConfigKey.UnitLevelCap).Value.ToInt());
            var maxLevel = unitLevelCap <= userUnit.Level || nextRank == null ? unitLevelCap : nextRank.RequireLevel;

            var currentSpecialAttack = TranslateSpecialAttack(userUnit, mstUnit);
            var detailModel = new UnitEnhanceUnitDetailModel(mstUnit.Detail);
            var abilities = UnitEnhanceAbilityModelListFactory.Create(mstUnit, userUnit.Rank);
            var statusModel = TranslateStatusModel(mstUnit, userUnit);
            var artworkRewardInfo = GetArtworkButtonStateAndArtworkId(mstUnit, userUnit);

            return new UnitEnhanceUnitInfoModel(
                characterImagePath,
                mstUnit.Name,
                mstUnit.RoleType,
                mstUnit.Rarity,
                userUnit.Level,
                maxLevel,
                new SeriesLogoImagePath(SeriesAssetPath.GetSeriesLogoPath(mstUnit.SeriesAssetKey.Value)),
                mstUnit.SummonCoolTime,
                mstUnit.SummonCost,
                mstUnit.Color,
                currentSpecialAttack,
                detailModel,
                abilities,
                statusModel,
                artworkRewardInfo.stateType,
                artworkRewardInfo.mstArtworkId);
        }

        public UserDataId GetUserUnitId(MasterDataId mstUnitId)
        {
            var userUnit = GameRepository.GetGameFetchOther().UserUnitModels.Find(unit => unit.MstUnitId == mstUnitId);
            return userUnit.UsrUnitId;
        }

        UnitEnhanceSpecialAttackModel TranslateSpecialAttack(UserUnitModel userUnit, MstCharacterModel mstUnit)
        {
            var specialAttackInfoModel = SpecialAttackInfoModelFactory.Create(mstUnit, userUnit);

            return new UnitEnhanceSpecialAttackModel(
                specialAttackInfoModel.Name,
                specialAttackInfoModel.Description,
                mstUnit.SpecialAttackInitialCoolTime.ToSpecialAttackCoolTime(),
                mstUnit.SpecialAttackCoolTime.ToSpecialAttackCoolTime(),
                mstUnit.RoleType);
        }

        UnitEnhanceUnitStatusModel TranslateStatusModel(MstCharacterModel mstUnit, UserUnitModel userUnit)
        {
            var calculateStatus = UnitStatusCalculateHelper.Calculate(mstUnit, userUnit.Level, userUnit.Rank, userUnit.Grade);
            return new UnitEnhanceUnitStatusModel(
                calculateStatus.HP,
                calculateStatus.AttackPower,
                mstUnit.AttackRangeType,
                mstUnit.UnitMoveSpeed);
        }

        (UnitEnhanceArtworkButtonStateType stateType, MasterDataId mstArtworkId) GetArtworkButtonStateAndArtworkId(
            MstCharacterModel mstUnit,
            UserUnitModel userUnit)
        {
            var gradeUpReward = MstUnitGradeUpRewardRepository.GetUnitGradeUpRewardFirstOrDefault(mstUnit.Id);

            // グレードアップ報酬が存在しない場合はボタン自体を非表示
            if (gradeUpReward.IsEmpty()) return (UnitEnhanceArtworkButtonStateType.Hidden, MasterDataId.Empty);
            // 報酬が原画でない場合はボタンを非表示
            if (gradeUpReward.ResourceType != ResourceType.Artwork) return (UnitEnhanceArtworkButtonStateType.Hidden, MasterDataId.Empty);

            // グレードアップ報酬のグレードが現在のグレード以下の場合はボタンを表示
            if (gradeUpReward.GradeLevel <= userUnit.Grade) return (UnitEnhanceArtworkButtonStateType.Visible, gradeUpReward.ResourceId);

            // グレードアップ報酬で原画が存在しており、ユニットのグレードが報酬のグレード未満の場合はボタンにロックを表示
            return (UnitEnhanceArtworkButtonStateType.Locked, gradeUpReward.ResourceId);
        }
    }
}
