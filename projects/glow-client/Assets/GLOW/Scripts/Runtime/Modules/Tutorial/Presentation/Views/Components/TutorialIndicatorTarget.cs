using UnityEngine;

namespace GLOW.Modules.Tutorial.Presentation.Views
{
    public class TutorialIndicatorTarget : MonoBehaviour
    {
        [SerializeField] string _targetName;

        public string TargetName => _targetName;

        public RectTransform GetRectTransform()
        {
            return (RectTransform)transform;
        }
    }
}
