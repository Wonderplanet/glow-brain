using System.Collections.Generic;
using UnityEngine;
using UnityEngine.Playables;

namespace GLOW.Scenes.InGame.Presentation.TimelineTracks
{
    public class TimelineLoopTrackMixerBehaviour : PlayableBehaviour
    {
        public PlayableDirector Director { get; set; }

        public override void ProcessFrame(Playable playable, FrameData info, object playerData)
        {
            // 実行中じゃないときはループさせない（Timelineの編集に不便すぎるので）
            if (!Application.isPlaying)
            {
                return;
            }

            var behaviours = GetBehaviours(playable);

            foreach (var behaviour in behaviours)
            {
                var clip = behaviour.Clip;
                var time = Director.time;

                if (time >= clip.start && (time >= clip.end || time >= Director.duration))
                {
                    Director.time = clip.start;
                }
            }
        }

        List<TimelineLoopTrackBehaviour> GetBehaviours(Playable playable)
        {
            var behaviours = new List<TimelineLoopTrackBehaviour>();

            var inputCount = playable.GetInputCount();
            for (int i = 0; i < inputCount; i++)
            {
                var inputPlayable = (ScriptPlayable<TimelineLoopTrackBehaviour>)playable.GetInput(i);
                behaviours.Add(inputPlayable.GetBehaviour());
            }

            return behaviours;
        }
    }
}
