using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Extensions;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle.AutoPlayer
{
    public class SequenceGroupSwitchableAutoPlayerSequencer
    {
        readonly List<AutoPlayerSequenceGroupModel> _autoPlayerSequenceGroupModels;
        AutoPlayerSequenceGroupModel _currentSequenceGroupModel;

        public AutoPlayerSequenceGroupModel CurrentSequenceGroupModel => _currentSequenceGroupModel;


        public IReadOnlyList<AutoPlayerSequenceElementStateModel> CurrentAutoPlayerSequenceElementStateModels =>
            _currentSequenceGroupModel.SequenceElementStateModels;

        public SequenceGroupSwitchableAutoPlayerSequencer(List<AutoPlayerSequenceElementStateModel> sequenceElementStateModels)
        {
            _autoPlayerSequenceGroupModels = sequenceElementStateModels
                .GroupBy(model => model.ElementModel.SequenceGroupId)
                .Select(model =>
                {
                    return new AutoPlayerSequenceGroupModel(
                        model.Key,
                        model.ToList(),
                        TickCount.Zero);
                })
                .ToList();
            _currentSequenceGroupModel = _autoPlayerSequenceGroupModels.FirstOrDefault(AutoPlayerSequenceGroupModel.Empty);
        }

        public void SetCurrentSequenceElementStateModels(List<AutoPlayerSequenceElementStateModel> updatedSequenceElementStateModels)
        {
            _currentSequenceGroupModel = _currentSequenceGroupModel with
            {
                SequenceElementStateModels = updatedSequenceElementStateModels
            };
        }

        public void SwitchSequenceGroup(AutoPlayerSequenceGroupId nextSequenceGroupId, TickCount activeStartTime)
        {
            var targetGroupModel = _autoPlayerSequenceGroupModels
                .FirstOrDefault(group => group.SequenceGroupId == nextSequenceGroupId);
            if(targetGroupModel == null)
            {
                return;
            }

            _currentSequenceGroupModel = targetGroupModel;

            _currentSequenceGroupModel = _currentSequenceGroupModel with
            {
                ActiveStartTime = activeStartTime
            };
        }
    }
}
