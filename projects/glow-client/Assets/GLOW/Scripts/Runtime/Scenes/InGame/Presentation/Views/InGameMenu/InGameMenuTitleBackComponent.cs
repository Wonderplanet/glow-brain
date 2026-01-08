using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.InGame.Domain.Constants;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Views.InGameMenu
{
    public class InGameMenuTitleBackComponent : UIObject
    {
        [SerializeField] UIObject _staminaAttention;    // 通常ステージ(メイン・イベントクエスト)
        [SerializeField] UIObject _challengeableCountAttention;  // 降臨バトル・強化クエスト
        [SerializeField] UIObject _pvpAttention;    // ランクマッチ

        public void SetUpAttention(InGameConsumptionType inGameConsumptionType, InGameTypePvpFlag isPvp)
        {
            _staminaAttention.IsVisible = !isPvp && inGameConsumptionType == InGameConsumptionType.Stamina;
            _challengeableCountAttention.IsVisible = !isPvp && inGameConsumptionType == InGameConsumptionType.ChallengeableCount;
            _pvpAttention.IsVisible = isPvp;
        }
    }
}
