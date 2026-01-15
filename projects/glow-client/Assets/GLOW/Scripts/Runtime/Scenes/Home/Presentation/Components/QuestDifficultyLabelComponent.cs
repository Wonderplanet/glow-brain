using System;
using System.Diagnostics.CodeAnalysis;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.Home.Presentation.Components
{
    public class QuestDifficultyLabelComponent : UIObject
    {
        [Serializable]
        [SuppressMessage("ReSharper", "InconsistentNaming")]
        public struct DifficultyPair
        {
            public Difficulty Difficulty;
            public GameObject DifficultyPlateObject;
        }
        [SerializeField] DifficultyPair[] _difficultyPairs;

        public void SetDifficulty(Difficulty difficulty)
        {
            foreach (var difficultyPair in _difficultyPairs)
            {
                difficultyPair.DifficultyPlateObject.SetActive(difficultyPair.Difficulty == difficulty);
            }
        }
    }
}
