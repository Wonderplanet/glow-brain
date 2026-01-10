using System;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.InteractionControls;
using Zenject;

namespace GLOW.Scenes.BeginnerMission.Presentation.View
{
    public class BeginnerMissionScreenInteractionControl : IScreenInteractionControl
    {
        [Inject] IBeginnerMissionMainControl BeginnerMissionMainControl { get; }

        int _lockCount;
        
        public void ActivityBegin()
        {
            if (_lockCount > 0)
            {
                ++_lockCount;
                return;
            }
            
            BeginnerMissionMainControl.SetIndicatorVisible(true);
            ++_lockCount;
        }

        public void ActivityEnd()
        {
            --_lockCount;
            if (_lockCount > 0)
            {
                return;
            }
            
            BeginnerMissionMainControl.SetIndicatorVisible(false);
        }

        IDisposable IScreenInteractionControl.Lock()
        {
            return this.Activate();
        }
    }
}