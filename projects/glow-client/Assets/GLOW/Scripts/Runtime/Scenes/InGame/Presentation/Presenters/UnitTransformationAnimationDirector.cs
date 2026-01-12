using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Extensions;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Presentation.Presenters
{
    public class UnitTransformationAnimationDirector
    {
        List<UnitTransformationAnimationInfo> _animationInfos = new();

        public void OnFieldObjectsUpdated(IReadOnlyList<CharacterUnitModel> unitModels)
        {
            // 変身するキャラがいたらUnitTransformationAnimationInfoを作成
            var infos = unitModels
                .Where(unit => unit.Action.ActionState == UnitActionState.Transformation)
                .Where(unit => _animationInfos.All(info => info.BeforeUnitId != unit.Id))
                .Select(CreateAnimationInfo);

            _animationInfos.AddRange(infos);
        }

        public void OnMangaAnimationStart(IReadOnlyList<MangaAnimationModel> mangaAnimationModels)
        {
            // 変身時に再生する原画演出をUnitTransformationAnimationInfoにセットしておく
            var groupedMangaAnimationModels = mangaAnimationModels
                .Where(IsTransformationMangaAnimation)
                .GroupBy(model => model.ConditionValue);

            foreach (var group in groupedMangaAnimationModels)
            {
                var autoPlayerSequenceElementId = group.Key.ToAutoPlayerSequenceElementId();

                var info = _animationInfos
                    .FirstOrDefault(info => info.BeforeUnitAutoPlayerSequenceElementId == autoPlayerSequenceElementId);
                if (info == null) continue;

                var updatedInfo = info with { MangaAnimationModels = group.ToList() };
                _animationInfos.Replace(info, updatedInfo);
            }
        }

        public bool RegisterTransformedUnitIfNeeded(CharacterUnitModel unitModel)
        {
            if (unitModel.Transformation.BeforeUnitId.IsEmpty()) return false;

            // 召喚されたキャラが変身したキャラなら、UnitTransformationAnimationInfoに変身後のCharacterUnitModelをセット
            var info = _animationInfos.FirstOrDefault(info => info.BeforeUnitId == unitModel.Transformation.BeforeUnitId);
            if (info == null) return false;

            var updatedInfo = info with { AfterUnitModel = unitModel };
            _animationInfos.Replace(info, updatedInfo);

            return true;
        }

        public List<UnitTransformationAnimationInfo> GetAnimationInfosThatCanBeStarted()
        {
            return _animationInfos
                .Where(info => info.AfterUnitModel != CharacterUnitModel.Empty)
                .ToList();
        }

        public void RemoveAnimationInfosThatCanBeStarted()
        {
            _animationInfos.RemoveAll(info => !info.AfterUnitModel.IsEmpty());
        }

        UnitTransformationAnimationInfo CreateAnimationInfo(CharacterUnitModel unitModel)
        {
            return new UnitTransformationAnimationInfo(
                unitModel.Id,
                unitModel.AutoPlayerSequenceElementId,
                CharacterUnitModel.Empty,
                new List<MangaAnimationModel>());
        }

        bool IsTransformationMangaAnimation(MangaAnimationModel model)
        {
            return model.ConditionType == MangaAnimationConditionType.TransformationReady ||
                   model.ConditionType == MangaAnimationConditionType.TransformationStart ||
                   model.ConditionType == MangaAnimationConditionType.TransformationEnd;
        }
    }
}
