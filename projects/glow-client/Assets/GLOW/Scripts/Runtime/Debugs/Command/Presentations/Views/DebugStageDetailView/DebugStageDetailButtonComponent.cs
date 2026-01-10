using GLOW.Core.Domain.ValueObjects.Stage;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Debugs.Command.Presentations.Views.DebugStageDetailView
{
    public class DebugStageDetailButtonComponent : MonoBehaviour
    {
        [SerializeField] Button _button;
        [SerializeField] Image _buttonImage;
        [SerializeField] Text _buttonText;

        public Button Button => _button;
        public Image ButtonImage => _buttonImage;
        public Text ButtonText => _buttonText;
        public StageNumber StageNumber { get; set; }
    }
}
