using System;
using GLOW.Core.Presentation.Components;

namespace GLOW.Scenes.GachaResult.Presentation.Views
{
    public class GachaResultCharacterIconComponent : UIObject
    {
        Action _convertAnimationAction;
        public void SetAction(Action convertAnimationAction)
        {
            _convertAnimationAction = convertAnimationAction;
        }

        void OnConvertAnimationAction()
        {
            _convertAnimationAction?.Invoke();
        }
    }
}
