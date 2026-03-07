using GLOW.Core.Domain.ValueObjects.Stage;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Debugs.Command.Presentations.Views.DebugStageDetailView
{
    public class DebugStageDetailContentComponent : MonoBehaviour
    {
        [SerializeField] ScrollRect _rewardScrollRect;
        [SerializeField] Text _text;
        public ScrollRect RewardScrollRect => _rewardScrollRect;
        public Text Text => _text;

        public StageNumber StageNumber { get; set; }
    }
}
