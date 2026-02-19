using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Core.Domain.Repositories
{
    public interface IMstInGameModel : IStageEnemyParameterCoef
    {
        MasterDataId MstInGameId { get; }
        InGameAssetKey InGameAssetKey { get; } // マスターデータ本体には居ない。実装クラス経由で別Mstからもらう
        InGameNumber InGameNumber { get; } // マスターデータ本体には居ない。実装クラス経由で別Mstからもらう
        InGameName InGameName { get; } // マスターデータ本体には居ない。実装クラス経由で別Mstからもらう
        StageResultTips ResultTips { get; }
        BGMAssetKey BGMAssetKey { get; }
        BGMAssetKey BossBGMAssetKey { get; }
        KomaBackgroundAssetKey LoopBackGroundAssetKey { get; }
        OutpostAssetKey PlayerOutpostAssetKey { get; }
        MasterDataId MstPageId { get; }
        MasterDataId MstEnemyOutpostId { get; }
        MasterDataId MstDefenseTargetId { get; }
        AutoPlayerSequenceSetId MstAutoPlayerSequenceSetId { get; }
        MasterDataId BossMstEnemyStageParameterId { get; }
        BossCount BossCount { get; }
        EventBonusGroupId EventBonusGroupId { get; }
        InGameConsumptionType InGameConsumptionType { get; }
    }
}
