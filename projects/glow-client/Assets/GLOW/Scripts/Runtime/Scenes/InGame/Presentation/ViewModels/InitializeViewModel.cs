using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models.BattleEndConditions;

namespace GLOW.Scenes.InGame.Presentation.ViewModels
{
    public record InitializeViewModel(
        StageNumberCaption StageNumberCaption,
        InGameName InGameName,
        IReadOnlyList<DeckUnitViewModel> DeckUnitViewModels,
        MstPageModel MstPageModel,
        OutpostModel PlayerOutpostModel,
        OutpostModel EnemyOutpostModel,
        RushModel RushModel,
        RushModel PvpOpponentRushModel,
        IReadOnlyList<CharacterUnitModel> InitialCharacterUnitModels, // 初期配置キャラ
        IReadOnlyList<InGameGimmickObjectModel> InitialInGameGimmickObjectModels,
        DefenseTargetModel DefenseTargetModel,
        BattlePointModel BattlePointModel,
        BattleSpeed BattleSpeed,
        InGameAutoEnabledFlag IsAutoEnabled,
        InGameType InGameType,
        QuestType QuestType,
        StageTimeModel StageTimeModel,
        BattleEndModel BattleEndModel);
}
