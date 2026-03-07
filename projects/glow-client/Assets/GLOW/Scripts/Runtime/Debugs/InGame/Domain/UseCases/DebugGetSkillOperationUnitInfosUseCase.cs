#if GLOW_INGAME_DEBUG
using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Debugs.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Constants;

using GLOW.Scenes.InGame.Domain.Models;
using Zenject;

namespace GLOW.Debugs.InGame.Domain.UseCases
{
    // デバッグ：スキル操作対象のユニット情報を取得
    public class DebugGetSkillOperationUnitInfosUseCase
    {
        [Inject] IInGameScene InGameScene { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }

        public IReadOnlyList<DebugSkillOperationUnitInfoModel> GetUnitInfos()
        {
            var result = new List<DebugSkillOperationUnitInfoModel>();

            // プレイヤーのDeckから取得
            foreach (var deckUnit in InGameScene.DeckUnits)
            {
                if(deckUnit.IsEmptyUnit()) {continue;}
                var character = MstCharacterDataRepository.GetCharacter(deckUnit.CharacterId);
                result.Add(new DebugSkillOperationUnitInfoModel(
                    deckUnit.CharacterId,
                    BattleSide.Player,
                    character.Name));
            }

            // 決闘の場合、敵のDeckも取得
            if (InGameScene.Type == InGameType.Pvp)
            {
                foreach (var deckUnit in InGameScene.PvpOpponentDeckUnits)
                {
                    if(deckUnit.IsEmptyUnit()) {continue;}
                    var character = MstCharacterDataRepository.GetCharacter(deckUnit.CharacterId);
                    result.Add(new DebugSkillOperationUnitInfoModel(
                        deckUnit.CharacterId,
                        BattleSide.Enemy,
                        character.Name));
                }
            }

            return result;
        }
    }
}
#endif



