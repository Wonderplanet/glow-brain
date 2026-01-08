using System;

namespace GLOW.Modules.Tutorial.Data.Data
{
    // ReSharper disable InconsistentNaming

    [Serializable]
    public class TutorialSequenceData
    {
        public string SequenceId;
        public string CallbackActionIdentifier;
        public string InvertMaskPositionIdentifier;
        public bool ShouldTapOnlyInvertMask;
        public bool DisplayTapIcon;
        public int TextPositionY;
        public string Text;
    }
}
