using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Modules.GameOption.Domain.Constants;
using GLOW.Modules.GameOption.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models.BattleEndConditions;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record InitializeResultModel(
        InGameNumber InGameNumber,
        InGameName InGameName,
        BGMAssetKey BGMAssetKey,
        BGMAssetKey BossBGMAssetKey,
        IReadOnlyList<DeckUnitModel> DeckUnits,
        TwoRowDeckModeFlag IsTwoRowDeck,
        MstPageModel MstPage,
        IReadOnlyDictionary<KomaId, KomaModel> KomaDictionary,
        OutpostModel PlayerOutpost,
        OutpostModel EnemyOutpost,
        RushModel RushModel,
        RushModel PvpOpponentRushModel,
        IReadOnlyList<CharacterUnitModel> InitialCharacterUnits, // 初期配置キャラ
        IReadOnlyList<InGameGimmickObjectModel> InGameGimmickObjectModels,
        DefenseTargetModel DefenseTargetModel, // 防衛オブジェクト
        BattlePointModel BattlePointModel,
        BattleSpeed BattleSpeed,
        InGameAutoEnabledFlag IsAutoEnabled,
        InGameType InGameType,
        QuestType QuestType,
        MangaAnimationAssetKey StartMangaAnimationAssetKey,
        MangaAnimationSpeed StartMangaAnimationSpeed,
        BattleStartNoiseAnimationNeedFlag NeedsBattleStartNoiseAnimation,
        StageTimeModel StageTimeModel,
        BattleEndModel BattleEndModel,
        SpecialAttackCutInPlayType SpecialAttackCutInPlayType,
        DamageDisplayFlag IsDamageDisplay,
        IReadOnlyList<MasterDataId> SpecialAttackCutInPlayedUnitIds,
        InitialLoadAssetsModel InitialLoadAssetsModel,
        InGameContinueSelectingFlag IsInGameContinueSelecting);
}
