using GLOW.Core.Domain.Constants;
using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.QuestSelect.Presentation.QuestDifficultySelect.Component
{
    public class QuestDifficultyButtonTextComponent : UIObject
    {
        [SerializeField] UIText _text;

        [SerializeField] string[] _difficultyNames = {};

        [SerializeField] Material[] _fontMaterials = { };

        [SerializeField] Material _defaultMaterial;

        public void Setup(Difficulty difficulty)
        {
            _text.SetText(GetName(difficulty));
            _text.SetMaterial(GetMaterial(difficulty));

            _text.Hidden = false;
        }

        string GetName(Difficulty difficulty)
        {
            var index = (int)difficulty;

            return index >= _difficultyNames.Length ? "" : _difficultyNames[index];
        }

        Material GetMaterial(Difficulty difficulty)
        {
            var index = (int)difficulty;

            return index >= _fontMaterials.Length ? _defaultMaterial : _fontMaterials[index];
        }
    }
}