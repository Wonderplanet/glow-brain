using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Evaluator;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.IdleIncentiveTop.Domain.Models;
using GLOW.Scenes.IdleIncentiveTop.Domain.ModelFactories;
using GLOW.Scenes.InGame.Domain.ModelFactories;
using WonderPlanet.RandomGenerator;
using Zenject;

namespace GLOW.Scenes.IdleIncentiveTop.Domain.UseCase
{
    public class GetIdleIncentiveTopStageUseCase
    {
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] IMstEnemyCharacterDataRepository MstEnemyCharacterDataRepository { get; }
        [Inject] IMstQuestDataRepository MstQuestDataRepository { get; }
        [Inject] IMstStageDataRepository MstStageDataRepository { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IAutoPlayerSequenceModelFactory AutoPlayerSequenceModelFactory { get; }
        [Inject] IStageOrderEvaluator StageOrderEvaluator { get; }
        [Inject] IRandomizer Randomizer { get; }
        [Inject] IMstConfigRepository MstConfigRepository { get; }
        [Inject] IIdleIncentiveTopPlayerUnitModelFactory IdleIncentiveTopPlayerUnitModelFactory { get; }

        public IdleIncentiveTopStageModel GetTopStageModel()
        {
            // プレイヤーのキャラ
            var playerUnit = GetPlayerUnit();
            var playerUnitModel = IdleIncentiveTopPlayerUnitModelFactory.Create(playerUnit);

            // ステージと敵キャラ
            var maxOrderClearedStage = StageOrderEvaluator.GetMaxOrderClearedStageWithNormalDifficulty();
            
            var enemyUnit = GetEnemyUnit(playerUnit, maxOrderClearedStage);
            var backgroundAssetKey = GetBackgroundAssetKey(maxOrderClearedStage);

            return new IdleIncentiveTopStageModel(
                playerUnitModel,
                enemyUnit,
                backgroundAssetKey);
        }

        MstCharacterModel GetPlayerUnit()
        {
            // アバターアイコンにしているキャラを表示
            var avatarUnitId = GameRepository.GetGameFetchOther().UserProfileModel.MstUnitId;
            return MstCharacterDataRepository.GetCharacter(avatarUnitId);
        }
        
        /// <summary>
        /// クリア済みの最新ステージからボス以外の敵キャラを取得する
        /// </summary>
        IdleIncentiveTopEnemyUnitModel GetEnemyUnit(MstCharacterModel mstUnit, MstStageModel stage)
        {
            if (mstUnit.RoleType == CharacterUnitRoleType.Special)
            {
                var specialEnemyAssetKey = new UnitAssetKey("enemy_glo_00901");
                
                return new IdleIncentiveTopEnemyUnitModel(
                    UnitImageAssetPath.FromAssetKey(specialEnemyAssetKey), 
                    PhantomizedFlag.False);
            }
            
            return CreateEnemyUnitModelFromStage(stage);
        }
        
        IdleIncentiveTopEnemyUnitModel CreateEnemyUnitModelFromStage(MstStageModel stage)
        {
            if (stage.MstAutoPlayerSequenceSetId.IsEmpty())
            {
                return CreateDefaultEnemyUnitModel();
            }

            var autoPlayerSequenceModel = AutoPlayerSequenceModelFactory.Create(stage.MstAutoPlayerSequenceSetId);
            var enemyCharacters = autoPlayerSequenceModel.SummonEnemies;

            if (!enemyCharacters.Any())
            {
                return CreateDefaultEnemyUnitModel();
            }

            // 通常の敵キャラクターをフィルタリング
            var normalEnemies = enemyCharacters.Where(enemy => enemy.Kind == CharacterUnitKind.Normal).ToList();
            
            if (normalEnemies.Any())
            {
                // 通常の敵がいる場合はランダムに選択
                var randomIndex = Randomizer.Range(0, normalEnemies.Count);
                var selectedEnemy = normalEnemies[randomIndex];
                return CreateEnemyUnitModel(selectedEnemy);
            }
            
            // ボス以外の敵がいない場合は、全ての敵からランダムに選択
            var allEnemiesRandomIndex = Randomizer.Range(0, enemyCharacters.Count);
            var selectedAllEnemy = enemyCharacters[allEnemiesRandomIndex];
            return CreateEnemyUnitModel(selectedAllEnemy);
        }
        
        IdleIncentiveTopEnemyUnitModel CreateDefaultEnemyUnitModel()
        {
            var config = MstConfigRepository.GetConfig(MstConfigKey.IdleIncentiveDefaultEnemyAssetKey);
            var defaultAssetKey = config.Value.ToUnitAssetKey();
            
            return new IdleIncentiveTopEnemyUnitModel(
                UnitImageAssetPath.FromAssetKey(defaultAssetKey),
                PhantomizedFlag.False);
        }
        
        IdleIncentiveTopEnemyUnitModel CreateEnemyUnitModel(MstEnemyStageParameterModel enemyCharacter)
        {
            var phantomizedFlag = PhantomizedFlag.False;
            
            if (!enemyCharacter.MstEnemyCharacterId.IsEmpty())
            {
                var enemyCharacterModel = MstEnemyCharacterDataRepository.GetEnemyCharacter(enemyCharacter.MstEnemyCharacterId);
                phantomizedFlag = enemyCharacterModel.IsPhantomized;
            }
            
            return new IdleIncentiveTopEnemyUnitModel(
                UnitImageAssetPath.FromAssetKey(enemyCharacter.AssetKey),
                phantomizedFlag);
        }

        KomaBackgroundAssetKey GetBackgroundAssetKey(MstStageModel stage)
        {
            if (stage.LoopBackGroundAssetKey.IsEmpty())
            {
                var config = MstConfigRepository.GetConfig(MstConfigKey.IdleIncentiveDefaultKomaBackgroundAssetKey);
                return config.Value.ToKomaBackgroundAssetKey();
            }

            return stage.LoopBackGroundAssetKey;
        }
    }
}
