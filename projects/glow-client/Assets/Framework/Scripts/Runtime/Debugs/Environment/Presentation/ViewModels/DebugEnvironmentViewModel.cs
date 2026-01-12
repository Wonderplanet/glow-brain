using System.Text;

namespace WPFramework.Debugs.Environment.Presentation.ViewModels
{
    public partial record DebugEnvironmentViewModel(string Env, string Name, string Description, string Api, bool IsLast, bool IsTarget)
    {
        public string Env { get; } = Env;
        public string Name { get; } = Name;
        public string Description { get; } = Description;
        public string Api { get; } = Api;
        public bool IsLast { get; } = IsLast;
        public bool IsTarget { get; } = IsTarget;

        public string EnvironmentText
        {
            get
            {
                var builder = new StringBuilder();
                if (IsTarget)
                {
                    builder.Append("<color=blue>");
                }

                if (IsLast)
                {
                    builder.Append("[前回選択]");
                }

                builder.Append($"{Env} ({Name})");

                if (IsTarget)
                {
                    builder.Append("</color>");
                }

                return builder.ToString();
            }
        }
    }
}
