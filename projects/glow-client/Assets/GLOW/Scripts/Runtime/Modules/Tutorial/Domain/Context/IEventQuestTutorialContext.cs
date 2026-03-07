using System;
using Cysharp.Threading.Tasks;

namespace GLOW.Modules.Tutorial.Domain.Context
{
    public interface IEventQuestTutorialContext
    {
        UniTask<bool> DoIfTutorial(Func<UniTask> action);
    }
}

