using GLOW.Scenes.QuestContentTop.Domain.enums;

namespace GLOW.Scenes.QuestContentTop.Presentation
{
    public interface IQuestContentTopViewControllerListener
    {
        void ScrollToContentCell(QuestContentTopElementType type);
    }
}