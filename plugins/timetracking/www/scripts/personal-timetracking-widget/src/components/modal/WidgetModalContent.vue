(<template>
    <div class="tlp-modal-body timetracking-details-modal-content">
        <div class="tlp-pane-section timetracking-details-modal-artifact-title">
            <widget-link-to-artifact
                v-bind:artifact="artifact"
            />
        </div>
        <div class="timetracking-details-modal-artifact-details">
            <widget-modal-artifact-info
                v-bind:artifact="artifact"
                v-bind:project="project"
            />
            <div class="timetracking-details-modal-artefact-link-top-bottom-spacer">
                <a class="timetracking-badge-direct-link-to-artifact timetracking-edit-link"
                   v-bind:href="timetracking_url">
                    {{ edit_time }}
                </a>
            </div>
            <widget-modal-table
                v-bind:time-data="timeData"
                v-bind:total-time="totalTime"
            />
        </div>
    </div>
</template>)
(<script>
import { gettext_provider } from "../../gettext-provider.js";
import WidgetModalArtifactInfo from "./WidgetModalArtifactInfo.vue";
import WidgetModalTable from "./WidgetModalTable.vue";
import WidgetLinkToArtifact from "../WidgetLinkToArtifact.vue";
export default {
    name: "WidgetModalContent",
    components: { WidgetLinkToArtifact, WidgetModalTable, WidgetModalArtifactInfo },
    props: {
        timeData: Array,
        totalTime: String
    },
    data() {
        const data = this.timeData[0];
        return {
            artifact: data.artifact,
            project: data.project
        };
    },
    computed: {
        edit_time: () => gettext_provider.gettext("Detailed times"),
        timetracking_url() {
            return this.artifact.html_url + "&view=timetracking";
        }
    }
};
</script>)
