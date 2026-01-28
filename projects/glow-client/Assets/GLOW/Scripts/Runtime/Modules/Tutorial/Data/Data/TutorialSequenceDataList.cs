using System.Collections.Generic;
using UnityEngine;
// ReSharper disable InconsistentNaming

namespace GLOW.Modules.Tutorial.Data.Data
{
    [ExcelAsset(AssetPath = "GLOW/AssetBundles/tutorial_sequence/", ExcelName = "^tutorial_sequence_main_part1")]
    public class TutorialSequenceDataList: ScriptableObject
    {
        public List<TutorialSequenceData> Entities;
    }
}
