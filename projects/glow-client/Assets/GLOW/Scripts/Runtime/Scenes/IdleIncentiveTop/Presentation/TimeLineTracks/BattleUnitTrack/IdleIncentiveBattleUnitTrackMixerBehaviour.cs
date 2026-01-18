using System.Collections.Generic;
using UnityEngine.Playables;

namespace GLOW.Scenes.IdleIncentiveTop.Presentation.TimeLineTracks
{
    public class IdleIncentiveBattleUnitTrackMixerBehaviour : PlayableBehaviour
    {
        public PlayableDirector Director { get; set; }

        public override void ProcessFrame(Playable playable, FrameData info, object playerData)
        {
            var clipDelegate = playerData as IIdleIncentiveBattleUnitTrackClipDelegate;
            if (null == clipDelegate) return;

            var behaviours = GetBehaviours(playable);
            var time = Director.time;

            foreach (var behaviour in behaviours)
            {
                if (time >= behaviour.Start && time <= behaviour.End && !behaviour.IsEnterClip)
                {
                    behaviour.IsEnterClip = true;
                    clipDelegate.OnPlay(behaviour.End - behaviour.Start);
                }
                else if(time > behaviour.End && behaviour.IsEnterClip)
                {
                    behaviour.IsEnterClip = false;
                }
            }
        }

        List<IdleIncentiveBattleUnitTrackBehaviour> GetBehaviours(Playable playable)
        {
            var behaviours = new List<IdleIncentiveBattleUnitTrackBehaviour>();

            var inputCount = playable.GetInputCount();
            for (int i = 0; i < inputCount; i++)
            {
                var inputPlayable = (ScriptPlayable<IdleIncentiveBattleUnitTrackBehaviour>)playable.GetInput(i);
                behaviours.Add(inputPlayable.GetBehaviour());
            }

            return behaviours;
        }
    }
}
